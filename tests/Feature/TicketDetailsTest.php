<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_creator_can_view_ticket_details(): void
    {
        $creator = User::factory()->create(['role' => 'agent']);
        $assignedAgent = User::factory()->create(['role' => 'agent']);

        $ticket = Ticket::factory()->create([
            'subject' => 'System Network Connectivity Outage',
            'description' => 'Detailed description of the network connectivity issue.',
            'category' => TicketCategory::TECHNICAL_SUPPORT,
            'status' => TicketStatus::OPEN,
            'priority' => TicketPriority::HIGH,
            'created_by' => $creator->id,
            'assigned_to' => $assignedAgent->id,
        ]);

        $response = $this->actingAs($creator)->get(route('tickets.show', $ticket));

        $response->assertStatus(200);
        $response->assertSee($ticket->ticket_number);
        $response->assertSee('System Network Connectivity Outage');
        $response->assertSee('Detailed description of the network connectivity issue.');
        $response->assertSee('Open');
        $response->assertSee('High');
        $response->assertSee('Technical Support');
        $response->assertSee($creator->name);
        $response->assertSee($assignedAgent->name);
    }

    public function test_admin_can_view_any_ticket(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create(['role' => 'agent']);

        $ticket = Ticket::factory()->create([
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($admin)->get(route('tickets.show', $ticket));

        $response->assertStatus(200);
    }

    public function test_agent_can_view_any_ticket(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $creator = User::factory()->create(['role' => 'agent']);

        $ticket = Ticket::factory()->create([
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($agent)->get(route('tickets.show', $ticket));

        $response->assertStatus(200);
    }

    public function test_ticket_policy_denies_unauthorized_user(): void
    {
        $creator = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'created_by' => $creator->id,
            'assigned_to' => null,
        ]);

        $unauthorizedUser = new User();
        $unauthorizedUser->forceFill(['id' => 9999, 'role' => 'other']);

        $policy = new \App\Policies\TicketPolicy();
        $this->assertFalse($policy->view($unauthorizedUser, $ticket));
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->get(route('tickets.show', $ticket));

        $response->assertRedirect(route('login'));
    }

    public function test_non_existent_ticket_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/tickets/99999');

        $response->assertStatus(404);
    }
}
