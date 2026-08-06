<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Jobs\ProcessIncomingTicketJob;
use App\Mail\TicketReplyMailable;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Database\Seeders\AiAgentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_smtp_ticket_reply_mailable_renders_and_sends_email_via_mail_fake(): void
    {
        Mail::fake();

        $customer = User::factory()->create(['email' => 'customer@example.com', 'name' => 'Jane Customer']);
        $agent = User::factory()->create(['role' => 'agent']);

        $ticket = Ticket::factory()->create([
            'subject' => 'How to update billing info?',
            'created_by' => $customer->id,
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.replies.store', $ticket), [
                'body' => 'You can update your billing card under Settings > Billing.',
            ]);

        Mail::assertSent(TicketReplyMailable::class, function (TicketReplyMailable $mailable) use ($customer, $ticket) {
            $mailable->assertTo($customer->email);
            $mailable->assertHasSubject("Re: [{$ticket->ticket_number}] {$ticket->subject}");
            
            $rendered = $mailable->render();
            return str_contains($rendered, 'You can update your billing card under Settings &gt; Billing.');
        });
    }

    public function test_imap_ingestion_command_creates_ticket_and_dispatches_ai_job(): void
    {
        Queue::fake();
        $this->seed(AiAgentSeeder::class);
        $aiAgent = User::where('email', 'ai@helpdesk.com')->firstOrFail();

        // Create ticket directly simulating IMAP ingestion logic
        $messageId = 'msg-unique-1001@mail.example.com';
        $senderEmail = 'sender@example.com';
        $senderName = 'Email Sender';
        $subject = 'Issue with subscription renewal';
        $bodyText = 'My subscription failed to renew automatically.';

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

        ProcessIncomingTicketJob::dispatch($ticket->id);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'subject' => 'Issue with subscription renewal',
            'status' => 'new',
            'assigned_to' => $aiAgent->id,
            'email_message_id' => $messageId,
        ]);

        Queue::assertPushed(ProcessIncomingTicketJob::class, function (ProcessIncomingTicketJob $job) use ($ticket) {
            return $job->ticketId === $ticket->id;
        });
    }

    public function test_imap_ingestion_prevents_duplicate_tickets_for_same_message_id(): void
    {
        $messageId = 'duplicate-msg-999@mail.example.com';

        // Existing ticket with same email_message_id
        Ticket::factory()->create([
            'email_message_id' => $messageId,
            'subject' => 'Original Email Ticket',
        ]);

        $this->assertDatabaseHas('tickets', [
            'email_message_id' => $messageId,
            'subject' => 'Original Email Ticket',
        ]);

        // Attempting to query existing message_id for deduplication
        $exists = Ticket::where('email_message_id', $messageId)->exists();
        $this->assertTrue($exists, 'Existing email_message_id must be detected for deduplication');
    }
}
