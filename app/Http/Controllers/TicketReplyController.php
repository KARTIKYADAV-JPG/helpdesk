<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class TicketReplyController extends Controller
{
    /**
     * Store a new reply for the specified ticket.
     */
    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('view', $ticket);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2'],
        ], [
            'body.required' => 'Please enter a reply message.',
            'body.min' => 'The reply message must be at least 2 characters.',
        ]);

        $user = $request->user();

        // Determine senderType based on user role (agent/admin vs customer)
        $senderType = ($user->isAgent() || $user->isAdmin()) ? 'agent' : 'customer';

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
            'sender_type' => $senderType,
        ]);

        // Send email via SMTP to customer
        if ($ticket->creator && !empty($ticket->creator->email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($ticket->creator->email)->send(new \App\Mail\TicketReplyMailable($ticket, $reply));
            } catch (\Throwable $e) {
                logger()->error('Failed to send ticket reply email: ' . $e->getMessage());
            }
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Reply submitted successfully.');
    }

    /**
     * Polish and improve a draft reply using Google AI Studio.
     */
    public function polish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2'],
        ], [
            'body.required' => 'Please enter a draft reply to polish.',
            'body.min' => 'The reply message must be at least 2 characters to polish.',
        ]);

        try {
            $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY') ?: env('GOOGLE_AI_API_KEY');
            $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-2.0-flash');

            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google AI API Key is missing. Please check your .env configuration.',
                ], 500);
            }

            $systemPrompt = "You are a professional customer support assistant.
Your task is to polish and improve customer support draft replies.
Requirements:
1. Maintain the exact original meaning and factual content of the draft reply.
2. Make the tone professional, clear, polite, and well-structured.
3. Return ONLY the polished response text itself. Do NOT include markdown code blocks, quotes, prefixes, or commentary.";

            $candidateModels = array_unique([
                $model,
                'gemini-2.0-flash',
                'gemini-2.5-flash',
                'gemini-2.0-flash-lite',
            ]);

            $polishedText = null;
            $lastErrorMessage = null;

            foreach ($candidateModels as $m) {
                $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => "{$systemPrompt}\n\nDraft Reply:\n{$validated['body']}"]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($text)) {
                        $polishedText = trim($text);
                        break;
                    }
                } else {
                    $data = $response->json();
                    $lastErrorMessage = $data['error']['message'] ?? $response->body();
                }
            }

            if (empty($polishedText)) {
                logger()->error('AI Reply Polishing failed', ['error' => $lastErrorMessage]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to polish reply at this time due to AI service limits. Please try again in a moment.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'polished_reply' => $polishedText,
            ]);

        } catch (\Throwable $e) {
            logger()->error('AI Polish Exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while polishing the reply.',
            ], 500);
        }
    }
}
