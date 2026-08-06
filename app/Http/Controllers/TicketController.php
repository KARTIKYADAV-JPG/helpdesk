<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'category', 'priority', 'assigned_to', 'search']);

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Validate allowed sort fields and directions
        $allowedFields = ['created_at', 'subject', 'status', 'priority'];
        $allowedDirections = ['asc', 'desc'];

        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'created_at';
        $sortDirection = in_array(strtolower($sortDirection), $allowedDirections) ? strtolower($sortDirection) : 'desc';

        $tickets = Ticket::with(['creator', 'assignedAgent'])
            ->filter($filters)
            ->sorted($sortField, $sortDirection)
            ->simplePaginate(15)
            ->withQueryString();

        // Dropdown values loaded directly from Enums
        $statuses = TicketStatus::values();
        $categories = TicketCategory::values();
        $priorities = TicketPriority::values();
        $agents = User::whereIn('role', ['agent', 'admin'])->orderBy('name')->get();

        return view('tickets.index', compact(
            'tickets',
            'sortField',
            'sortDirection',
            'filters',
            'statuses',
            'categories',
            'priorities',
            'agents'
        ));
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket): View
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['creator', 'assignedAgent', 'replies.user']);
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        $statusLabels = TicketStatus::labels();
        $categoryLabels = TicketCategory::labels();

        return view('tickets.show', compact('ticket', 'agents', 'statusLabels', 'categoryLabels'));
    }

    /**
     * Update the ticket status, category, or assignment.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        if (!$request->has('assignedToId') && $request->has('assigned_to')) {
            $request->merge(['assignedToId' => $request->input('assigned_to')]);
        }

        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(TicketStatus::class)],
            'category' => ['nullable', Rule::enum(TicketCategory::class)],
            'assignedToId' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'agent');
                }),
            ],
        ], [
            'status.enum' => 'The selected status is invalid.',
            'category.enum' => 'The selected category is invalid.',
            'assignedToId.exists' => 'The selected user is not a valid agent.',
        ]);

        $updateData = array_filter([
            'status' => $validated['status'] ?? null,
            'category' => $validated['category'] ?? null,
            'assigned_to' => $validated['assignedToId'] ?? null,
        ], fn($val) => !is_null($val));

        if (!empty($updateData)) {
            $ticket->update($updateData);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    /**
     * Assign the ticket to an agent.
     */
    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        if (!$request->has('assignedToId') && $request->has('assigned_to')) {
            $request->merge(['assignedToId' => $request->input('assigned_to')]);
        }

        $validated = $request->validate([
            'assignedToId' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'agent');
                }),
            ],
        ], [
            'assignedToId.required' => 'Please select an agent to assign.',
            'assignedToId.exists' => 'The selected user is not a valid agent.',
        ]);

        $ticket->update([
            'assigned_to' => $validated['assignedToId'],
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket assigned successfully.');
    }

    /**
     * Generate an AI summary for the ticket and save it.
     */
    public function summarize(Ticket $ticket): JsonResponse
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['creator', 'replies.user']);

        try {
            $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY') ?: env('GOOGLE_AI_API_KEY');
            $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-2.0-flash');

            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google AI API Key is missing. Please check your .env configuration.',
                ], 500);
            }

            $formattedReplies = $ticket->replies->map(function ($r, $index) {
                $sender = $r->senderType ?? 'user';
                $name = $r->user->name ?? 'User';
                return "Reply #" . ($index + 1) . " ({$sender} - {$name}): {$r->body}";
            })->implode("\n");

            $prompt = "Subject: {$ticket->subject}\nDescription: {$ticket->description}\n\nConversation History:\n" . ($formattedReplies ?: 'No replies yet.');

            $systemPrompt = "You are a customer support AI assistant.
Your task is to summarize the ticket issue and its conversation history into a clear, concise bulleted or short paragraph summary (2-4 bullet points or sentences).
Focus on:
1. Core issue reported by the customer.
2. Key actions taken or responses provided so far.
3. Current status or next steps.
Return ONLY the clean summary text without markdown headers or fluff.";

            $candidateModels = array_unique([
                $model,
                'gemini-2.0-flash',
                'gemini-2.5-flash',
                'gemini-2.0-flash-lite',
            ]);

            $summaryText = null;
            $lastErrorMessage = null;

            foreach ($candidateModels as $m) {
                $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => "{$systemPrompt}\n\n{$prompt}"]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($text)) {
                        $summaryText = trim($text);
                        break;
                    }
                } else {
                    $data = $response->json();
                    $lastErrorMessage = $data['error']['message'] ?? $response->body();
                }
            }

            if (empty($summaryText)) {
                logger()->error('AI Ticket Summarization failed', ['error' => $lastErrorMessage]);

                if ($ticket->summary) {
                    return response()->json([
                        'success' => true,
                        'summary' => $ticket->summary,
                        'warning' => 'Showing saved summary.',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to generate AI summary at this time. Please try again in a few moments.',
                ], 500);
            }

            // Save the generated summary with the ticket
            $ticket->update(['summary' => $summaryText]);

            return response()->json([
                'success' => true,
                'summary' => $summaryText,
            ]);

        } catch (\Throwable $e) {
            logger()->error('AI Summarization Exception: ' . $e->getMessage());

            if ($ticket->summary) {
                return response()->json([
                    'success' => true,
                    'summary' => $ticket->summary,
                    'warning' => 'AI service temporarily unavailable. Displaying saved summary.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the AI summary.',
            ], 500);
        }
    }
}
