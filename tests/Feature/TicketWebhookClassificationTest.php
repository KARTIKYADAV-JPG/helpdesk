<?php

namespace Tests\Feature;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Jobs\ClassifyTicketJob;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketWebhookClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_creates_ticket_and_dispatches_classification_job_immediately(): void
    {
        Queue::fake();

        $payload = [
            'subject' => 'How can I change my account password?',
            'description' => 'I cannot find the password change page in my settings.',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'John Doe',
        ];

        $response = $this->postJson('/api/webhooks/tickets', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Ticket created successfully and queued for background processing.',
        ]);

        $this->assertDatabaseHas('tickets', [
            'subject' => 'How can I change my account password?',
        ]);

        $ticket = Ticket::where('subject', 'How can I change my account password?')->first();
        $this->assertNotNull($ticket);

        Queue::assertPushed(\App\Jobs\ProcessIncomingTicketJob::class, function (\App\Jobs\ProcessIncomingTicketJob $job) use ($ticket) {
            return $job->ticketId === $ticket->id;
        });
    }

    public function test_classify_ticket_job_classifies_password_reset_question_as_general_question(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'General Question']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'subject' => 'How can I change my account password?',
            'description' => 'Need help resetting my user password.',
            'category' => TicketCategory::TECHNICAL_SUPPORT,
            'created_by' => $user->id,
        ]);

        $job = new ClassifyTicketJob($ticket->id);
        $job->handle();

        $ticket->refresh();

        $this->assertEquals(TicketCategory::GENERAL_QUESTION, $ticket->category);
        $this->assertEquals('General Question', $ticket->category->value);
    }

    public function test_classify_ticket_job_handles_api_failure_and_throws_exception_for_retry(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Quota exceeded']
            ], 429),
        ]);

        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'subject' => 'Billing inquiry',
            'created_by' => $user->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('AI Ticket classification service unavailable');

        $job = new ClassifyTicketJob($ticket->id);
        $job->handle();
    }

    public function test_webhook_validation_requires_subject(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/webhooks/tickets', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subject']);

        Queue::assertNothingPushed();
    }
}
