<?php

namespace App\Jobs;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessIncomingTicketJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 5;

    /**
     * The ticket ID to process.
     *
     * @var int
     */
    public int $ticketId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ticket = Ticket::with('creator')->find($this->ticketId);

        if (!$ticket) {
            Log::warning("ProcessIncomingTicketJob: Ticket #{$this->ticketId} not found.");
            return;
        }

        // Retrieve AI agent user
        $aiAgent = User::where('email', 'ai@helpdesk.com')->first();
        $aiAgentId = $aiAgent ? $aiAgent->id : null;

        // 1. Transition ticket status to 'processing' while AI processes
        $ticket->update(['status' => TicketStatus::PROCESSING]);

        // Run category classification
        try {
            (new ClassifyTicketJob($ticket->id))->handle();
        } catch (\Throwable $e) {
            Log::warning("ProcessIncomingTicketJob: Ticket classification warning: " . $e->getMessage());
        }

        // 2. Read Knowledge Base file
        $kbPath = storage_path('app/knowledge-base.md');
        if (!File::exists($kbPath)) {
            Log::error("ProcessIncomingTicketJob: Knowledge base file missing at {$kbPath}");
            $ticket->update([
                'status' => TicketStatus::OPEN,
                'assigned_to' => null,
            ]);
            return;
        }

        $knowledgeBaseContent = File::get($kbPath);

        // 3. Prepare Gemini AI prompt
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY') ?: env('GOOGLE_AI_API_KEY');
        $configuredModel = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.5-flash');

        if (empty($apiKey)) {
            Log::error("ProcessIncomingTicketJob: Gemini API key is missing.");
            $ticket->update([
                'status' => TicketStatus::OPEN,
                'assigned_to' => null,
            ]);
            return;
        }

        $prompt = "You are an AI customer support resolution agent.
You MUST rely EXCLUSIVELY on the provided Knowledge Base below to answer the ticket.
DO NOT use general knowledge. DO NOT guess or invent facts not stated in the Knowledge Base.
If the Knowledge Base does NOT contain an exact, definitive answer to the customer's issue, you MUST set can_answer to false.

KNOWLEDGE BASE:
---
{$knowledgeBaseContent}
---

CUSTOMER TICKET:
Subject: {$ticket->subject}
Description: {$ticket->description}

Task:
Determine if the customer's question can be answered using ONLY the provided Knowledge Base.
Return ONLY a valid JSON object matching this structure without markdown code blocks:
{\"can_answer\": true, \"solution\": \"Step-by-step clear solution text extracted from the Knowledge Base\"}
OR
{\"can_answer\": false, \"solution\": \"\"}";

        $candidateModels = array_unique(array_filter([
            $configuredModel,
            'gemini-3.5-flash',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-1.5-flash',
        ]));

        $aiResponseText = null;
        $lastError = null;

        foreach ($candidateModels as $model) {
            try {
                $response = Http::timeout(35)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($text)) {
                        $aiResponseText = trim($text);
                        break;
                    }
                } else {
                    $data = $response->json();
                    $lastError = $data['error']['message'] ?? $response->body();
                    Log::warning("ProcessIncomingTicketJob: Model {$model} failed: {$lastError}");
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("ProcessIncomingTicketJob: Model {$model} exception: {$lastError}");
            }
        }

        if (empty($aiResponseText)) {
            Log::error("ProcessIncomingTicketJob: AI resolution service failed for Ticket #{$this->ticketId}. Last error: {$lastError}");
            $ticket->update([
                'status' => TicketStatus::OPEN,
                'assigned_to' => null,
            ]);
            throw new \RuntimeException("AI Ticket resolution service unavailable: {$lastError}");
        }

        // Parse JSON response from Gemini
        $parsed = json_decode($aiResponseText, true);
        
        if (!$parsed && preg_match('/\{.*\}/s', $aiResponseText, $matches)) {
            $parsed = json_decode($matches[0], true);
        }

        $canAnswer = filter_var($parsed['can_answer'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $solution = trim($parsed['solution'] ?? '');

        if ($canAnswer && !empty($solution)) {
            // Extract customer first name
            $fullName = $ticket->creator->name ?? 'Customer';
            $firstName = trim(explode(' ', $fullName)[0]);
            if (empty($firstName)) {
                $firstName = 'Customer';
            }

            // Formatted AI resolution reply with required template
            $formattedReply = "Hi {$firstName},\n\nThank you for contacting us.\n\n{$solution}\n\nPlease let us know if you need further assistance.\n\nCode with Mosh Support";

            // Find AI agent or fallback agent for reply user_id
            $replyUser = $aiAgent ?? $ticket->creator;

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $replyUser->id,
                'body' => $formattedReply,
                'sender_type' => 'agent',
            ]);

            // Send notification email via SMTP
            if ($ticket->creator && !empty($ticket->creator->email)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($ticket->creator->email)->send(new \App\Mail\TicketReplyMailable($ticket, $reply));
                } catch (\Throwable $e) {
                    Log::error("ProcessIncomingTicketJob: Failed to send AI resolution email: " . $e->getMessage());
                }
            }

            // Keep assigned_to as AI agent and set status resolved + resolved_at
            $ticket->update([
                'status' => TicketStatus::RESOLVED,
                'resolved_at' => now(),
                'assigned_to' => $aiAgentId ?? $ticket->assigned_to,
            ]);
            Log::info("ProcessIncomingTicketJob: Ticket #{$this->ticketId} successfully resolved by AI.");
        } else {
            // Unresolvable via knowledge base -> set status to 'open' and remove AI assignment
            $ticket->update([
                'status' => TicketStatus::OPEN,
                'assigned_to' => null,
            ]);
            Log::info("ProcessIncomingTicketJob: Ticket #{$this->ticketId} cannot be resolved via knowledge base. Status set to 'open' and assignment cleared.");
        }
    }
}
