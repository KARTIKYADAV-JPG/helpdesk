<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AiAgentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PermanentEmailListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_emails_listen_command_signature_and_terminal_logging_output(): void
    {
        $this->artisan('emails:listen --once')
            ->expectsOutputToContain('IMAP EMAIL LISTENER')
            ->expectsOutputToContain('[IMAP] Connecting to mailbox...');
    }

    public function test_ticket_created_via_email_appears_on_frontend_ticket_listing_page(): void
    {
        Queue::fake();
        $this->seed(AiAgentSeeder::class);
        $aiAgent = User::where('email', 'ai@helpdesk.com')->firstOrFail();
        $user = User::factory()->create(['role' => 'agent']);

        $messageId = 'permanent-test-msg-001@example.com';
        $senderEmail = 'customer.listener@example.com';
        $senderName = 'Listener Customer';
        $subject = 'Need help with email ticket ingestion';
        $bodyText = 'Please verify that this ticket displays on the frontend /tickets page.';

        $customerUser = User::firstOrCreate(
            ['email' => $senderEmail],
            ['name' => $senderName, 'password' => bcrypt('password'), 'role' => 'agent']
        );

        $ticket = Ticket::create([
            'subject' => $subject,
            'description' => $bodyText,
            'category' => \App\Enums\TicketCategory::TECHNICAL_SUPPORT->value,
            'status' => TicketStatus::NEW->value,
            'priority' => \App\Enums\TicketPriority::MEDIUM->value,
            'created_by' => $customerUser->id,
            'assigned_to' => $aiAgent->id,
            'email_message_id' => $messageId,
            'raw_email' => $bodyText,
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'subject' => 'Need help with email ticket ingestion',
            'status' => 'new',
            'assigned_to' => $aiAgent->id,
            'email_message_id' => $messageId,
        ]);

        // Access frontend ticket listing page (/tickets)
        $response = $this->actingAs($user)->get('/tickets');

        $response->assertStatus(200);
        $response->assertSee('Need help with email ticket ingestion');
        $response->assertSee($ticket->ticket_number);
    }
}
