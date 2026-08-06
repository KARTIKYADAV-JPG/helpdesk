<?php

namespace Tests\Feature;

use App\Mail\TicketReplyMailable;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Tests\TestCase;

class EmailTerminalLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_emails_fetch_command_signature_and_terminal_logging_output(): void
    {
        $this->artisan('emails:fetch')
            ->expectsOutputToContain('[IMAP LOG] Connecting to mailbox');
    }

    public function test_smtp_mail_sending_listener_dispatches_events_on_mail_send(): void
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        $customer = User::factory()->create(['email' => 'terminaltest@example.com']);
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);
        $reply = TicketReply::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $customer->id]);

        \Illuminate\Support\Facades\Mail::to($customer->email)->send(new TicketReplyMailable($ticket, $reply));

        Event::assertDispatched(MessageSending::class);
        Event::assertDispatched(MessageSent::class);
    }
}
