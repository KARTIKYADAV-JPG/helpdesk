<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_can_be_assigned_to_an_agent(): void
    {
        $creator = User::factory()->create(['role' => 'agent']);
        $agent = User::factory()->create(['role' => 'agent']);

        $ticket = Ticket::factory()->create([
            'created_by' => $creator->id,
            'assigned_to' => null,
        ]);

        $this->assertNull($ticket->assigned_to);
        $this->assertNull($ticket->assignedAgent);

        $ticket->update(['assigned_to' => $agent->id]);
        $ticket->refresh();

        $this->assertEquals($agent->id, $ticket->assigned_to);
        $this->assertNotNull($ticket->assignedAgent);
        $this->assertEquals($agent->name, $ticket->assignedAgent->name);
    }
}
