<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketStatusCategoryUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_enum_labels_are_in_title_case(): void
    {
        $statusLabels = TicketStatus::labels();
        $categoryLabels = TicketCategory::labels();

        $this->assertEquals('In Progress', $statusLabels[TicketStatus::IN_PROGRESS->value]);
        $this->assertEquals('Resolved', $statusLabels[TicketStatus::RESOLVED->value]);
        $this->assertEquals('Technical Support', $categoryLabels[TicketCategory::TECHNICAL_SUPPORT->value]);
        $this->assertEquals('Billing', $categoryLabels[TicketCategory::BILLING->value]);
    }

    public function test_agent_can_update_ticket_status_and_category(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create([
            'created_by' => $agent->id,
            'status' => TicketStatus::OPEN,
            'category' => TicketCategory::BILLING,
        ]);

        $response = $this->actingAs($agent)->patch(route('tickets.update', $ticket), [
            'status' => TicketStatus::RESOLVED->value,
            'category' => TicketCategory::TECHNICAL_SUPPORT->value,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $response->assertSessionHas('success', 'Ticket updated successfully.');

        $ticket->refresh();
        $this->assertEquals(TicketStatus::RESOLVED, $ticket->status);
        $this->assertEquals(TicketCategory::TECHNICAL_SUPPORT, $ticket->category);
    }

    public function test_update_fails_with_invalid_status_or_category(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        $response = $this->actingAs($agent)->patch(route('tickets.update', $ticket), [
            'status' => 'INVALID_STATUS',
            'category' => 'INVALID_CATEGORY',
        ]);

        $response->assertSessionHasErrors(['status', 'category']);
    }

    public function test_unauthorized_user_cannot_update_ticket(): void
    {
        $creator = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $creator->id]);

        $unauthorizedUser = new User();
        $unauthorizedUser->forceFill(['id' => 9999, 'role' => 'customer']);

        $response = $this->actingAs($unauthorizedUser)->patch(route('tickets.update', $ticket), [
            'status' => TicketStatus::CLOSED->value,
        ]);

        $response->assertStatus(403);
    }
}
