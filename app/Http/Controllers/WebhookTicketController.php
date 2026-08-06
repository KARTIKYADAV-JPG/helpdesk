<?php

namespace App\Http\Controllers;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Jobs\ProcessIncomingTicketJob;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WebhookTicketController extends Controller
{
    /**
     * Handle incoming webhook requests to create a ticket and dispatch background processing job.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'customer_email' => ['nullable', 'email'],
            'email' => ['nullable', 'email'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', Rule::enum(TicketPriority::class)],
            'category' => ['nullable', 'string', Rule::enum(TicketCategory::class)],
        ], [
            'subject.required' => 'The ticket subject field is required.',
        ]);

        // Resolve description from description, body, or content
        $description = $validated['description'] 
            ?? $validated['body'] 
            ?? $validated['content'] 
            ?? null;

        // Resolve creator user ID
        $userId = $this->resolveCreatorUserId($request);

        // Determine default priority and category
        $priority = $validated['priority'] ?? TicketPriority::MEDIUM->value;
        $category = $validated['category'] ?? TicketCategory::TECHNICAL_SUPPORT->value;

        // Retrieve AI agent user for automatic initial assignment
        $aiAgent = User::where('email', 'ai@helpdesk.com')->first();

        // Create the ticket record in PostgreSQL database with initial status "new" and assigned to AI agent
        $ticket = Ticket::create([
            'subject' => $validated['subject'],
            'description' => $description,
            'category' => $category,
            'status' => TicketStatus::NEW->value,
            'priority' => $priority,
            'created_by' => $userId,
            'assigned_to' => $aiAgent ? $aiAgent->id : null,
        ]);

        // Dispatch background job to process & auto-resolve ticket via Knowledge Base & Gemini AI
        ProcessIncomingTicketJob::dispatch($ticket->id);

        // Return fast success response immediately
        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully and queued for background processing.',
            'data' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status instanceof TicketStatus ? $ticket->status->value : $ticket->status,
                'category' => $ticket->category instanceof TicketCategory ? $ticket->category->value : $ticket->category,
                'assigned_to' => $ticket->assigned_to,
                'created_at' => $ticket->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Resolve or create the user ID for ticket creation.
     */
    protected function resolveCreatorUserId(Request $request): int
    {
        if ($request->filled('created_by')) {
            $user = User::find($request->input('created_by'));
            if ($user) {
                return $user->id;
            }
        }

        $email = $request->input('customer_email') ?? $request->input('email');
        if ($email) {
            $name = $request->input('customer_name') ?? $request->input('name') ?? 'Webhook Customer';
            $user = User::firstOrCreate(
                ['email' => strtolower($email)],
                [
                    'name' => $name,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'agent',
                ]
            );
            return $user->id;
        }

        // Fallback to first user in system or create default webhook user
        $defaultUser = User::first();
        if ($defaultUser) {
            return $defaultUser->id;
        }

        $systemUser = User::create([
            'name' => 'System Webhook User',
            'email' => 'webhook@helpdesk.com',
            'password' => Hash::make(Str::random(16)),
            'role' => 'agent',
        ]);

        return $systemUser->id;
    }
}
