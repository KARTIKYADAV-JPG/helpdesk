<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_assign_ticket_to_valid_agent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create(['role' => 'agent']);
        $agent = User::factory()->create(['role' => 'agent']);

        $ticket = Ticket::factory()->create([
            'created_by' => $creator->id,
            'assigned_to' => null,
        ]);

        $response = $this->actingAs($admin)->patch(route('tickets.assign', $ticket), [
            'assignedToId' => $agent->id,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $response->assertSessionHas('success', 'Ticket assigned successfully.');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $agent->id,
        ]);
    }

    public function test_assign_fails_when_assigned_to_id_is_missing(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        $response = $this->actingAs($agent)->patch(route('tickets.assign', $ticket), [
            'assignedToId' => '',
        ]);

        $response->assertSessionHasErrors('assignedToId');
    }

    public function test_assign_fails_when_user_does_not_exist(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        $response = $this->actingAs($agent)->patch(route('tickets.assign', $ticket), [
            'assignedToId' => 999999,
        ]);

        $response->assertSessionHasErrors('assignedToId');
    }

    public function test_assign_fails_when_user_is_not_an_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $nonAgentUser = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        $response = $this->actingAs($agent)->patch(route('tickets.assign', $ticket), [
            'assignedToId' => $nonAgentUser->id,
        ]);

        $response->assertSessionHasErrors('assignedToId');
    }
}
