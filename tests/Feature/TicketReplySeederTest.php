<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketReply;
use Database\Seeders\TicketReplySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketReplySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_reply_seeder_populates_20_alternating_replies_for_ticket_110(): void
    {
        $this->seed(TicketReplySeeder::class);

        $ticket = Ticket::find(110);
        $this->assertNotNull($ticket);

        $replies = $ticket->replies;
        $this->assertCount(20, $replies);

        foreach ($replies as $index => $reply) {
            // Check alternating sender_type
            $expectedSender = ($index % 2 === 0) ? 'customer' : 'agent';
            $this->assertEquals($expectedSender, $reply->sender_type);
            $this->assertEquals($expectedSender, $reply->senderType);

            // Check that each reply has at least 10 lines
            $lineCount = count(explode("\n", trim($reply->body)));
            $this->assertGreaterThanOrEqual(10, $lineCount, "Reply #{$index} has fewer than 10 lines.");
        }
    }
}
