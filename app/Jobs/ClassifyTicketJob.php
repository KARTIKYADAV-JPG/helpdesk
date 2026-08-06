<?php

namespace App\Jobs;

use App\Enums\TicketCategory;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClassifyTicketJob implements ShouldQueue
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
     * The ticket ID to classify.
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
        $ticket = Ticket::find($this->ticketId);

        if (!$ticket) {
            Log::warning("ClassifyTicketJob: Ticket #{$this->ticketId} not found.");
            return;
        }

        $categories = TicketCategory::values();
        $categoryList = implode(", ", $categories);

        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY') ?: env('GOOGLE_AI_API_KEY');
        $configuredModel = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-2.0-flash');

        if (empty($apiKey)) {
            Log::error("ClassifyTicketJob: Gemini API key is not configured.");
            return;
        }

        $systemPrompt = "You are an AI customer support assistant.
Classify the following ticket into EXACTLY ONE of these categories:
[{$categoryList}]

Subject: {$ticket->subject}
Description: {$ticket->description}

Return ONLY the exact category name matching one of the options listed above, without any quotes, markdown headers, or explanation.";

        $candidateModels = array_unique(array_filter([
            $configuredModel,
            'gemini-3.5-flash',
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-1.5-flash',
        ]));

        $aiResponseText = null;
        $lastError = null;

        foreach ($candidateModels as $model) {
            try {
                $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt]
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
                    Log::warning("ClassifyTicketJob: Model {$model} failed: {$lastError}");
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("ClassifyTicketJob: Model {$model} exception: {$lastError}");
            }
        }

        if (empty($aiResponseText)) {
            Log::error("ClassifyTicketJob: AI classification failed for Ticket #{$this->ticketId}. Last error: {$lastError}");
            throw new \RuntimeException("AI Ticket classification service unavailable: {$lastError}");
        }

        $matchedCategory = $this->matchCategory($aiResponseText);

        if ($matchedCategory) {
            $ticket->update(['category' => $matchedCategory]);
            Log::info("ClassifyTicketJob: Ticket #{$this->ticketId} successfully classified as '{$matchedCategory->value}'.");
        } else {
            Log::warning("ClassifyTicketJob: Could not map AI response '{$aiResponseText}' to an existing TicketCategory enum for Ticket #{$this->ticketId}.");
        }
    }

    /**
     * Match AI text response to a TicketCategory enum case.
     */
    protected function matchCategory(string $text): ?TicketCategory
    {
        $textClean = trim(preg_replace('/[^a-zA-Z\s]/', '', $text));

        // Exact match check (case-insensitive)
        foreach (TicketCategory::cases() as $case) {
            if (strcasecmp($case->value, trim($text)) === 0 || strcasecmp($case->value, $textClean) === 0) {
                return $case;
            }
        }

        // Substring / partial match check
        foreach (TicketCategory::cases() as $case) {
            if (stripos($text, $case->value) !== false) {
                return $case;
            }
        }

        return null;
    }
}
