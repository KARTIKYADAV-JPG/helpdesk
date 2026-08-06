<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Jobs\ProcessIncomingTicketJob;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutoResolveTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolvable_password_reset_ticket_is_automatically_resolved_with_formatted_reply(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'can_answer' => true,
                                    'solution' => "1. Go to the login page and click 'Forgot Password'.\n2. Enter your email to receive a reset link.\n3. Open the link and set a new password."
                                ])]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $user = User::factory()->create(['name' => 'John Doe']);
        $ticket = Ticket::factory()->create([
            'subject' => 'How do I reset my password?',
            'description' => 'I forgot my password and need help resetting it.',
            'status' => TicketStatus::NEW,
            'created_by' => $user->id,
        ]);

        $job = new ProcessIncomingTicketJob($ticket->id);
        $job->handle();

        $ticket->refresh();

        $this->assertEquals(TicketStatus::RESOLVED, $ticket->status);
        $this->assertEquals('resolved', $ticket->status->value);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
        ]);

        $reply = $ticket->replies()->first();
        $this->assertNotNull($reply);
        $this->assertStringContainsString('Hi John,', $reply->body);
        $this->assertStringContainsString('Thank you for contacting us.', $reply->body);
        $this->assertStringContainsString('Forgot Password', $reply->body);
        $this->assertStringContainsString('Please let us know if you need further assistance.', $reply->body);
        $this->assertStringContainsString('Code with Mosh Support', $reply->body);
    }

    public function test_resolvable_credit_card_ticket_is_automatically_resolved(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'can_answer' => true,
                                    'solution' => "Navigate to Account Settings > Billing & Invoices and click 'Edit Payment Method' to enter new credit card details."
                                ])]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $user = User::factory()->create(['name' => 'Sarah Connor']);
        $ticket = Ticket::factory()->create([
            'subject' => 'How can I update my billing credit card?',
            'description' => 'My card expired and I need to update it.',
            'status' => TicketStatus::NEW,
            'created_by' => $user->id,
        ]);

        $job = new ProcessIncomingTicketJob($ticket->id);
        $job->handle();

        $ticket->refresh();

        $this->assertEquals(TicketStatus::RESOLVED, $ticket->status);
        $this->assertCount(1, $ticket->replies);
        $this->assertStringContainsString('Hi Sarah,', $ticket->replies->first()->body);
    }

    public function test_unresolvable_discount_coupon_ticket_transitions_to_open_without_ai_reply(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'can_answer' => false,
                                    'solution' => ''
                                ])]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $user = User::factory()->create(['name' => 'Alice Smith']);
        $ticket = Ticket::factory()->create([
            'subject' => 'Can you provide a discount coupon?',
            'description' => 'Looking for promo codes for summer sale.',
            'status' => TicketStatus::NEW,
            'created_by' => $user->id,
        ]);

        $job = new ProcessIncomingTicketJob($ticket->id);
        $job->handle();

        $ticket->refresh();

        $this->assertEquals(TicketStatus::OPEN, $ticket->status);
        $this->assertEquals('open', $ticket->status->value);
        $this->assertCount(0, $ticket->replies);
    }
}
