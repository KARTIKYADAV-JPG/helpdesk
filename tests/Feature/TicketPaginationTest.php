<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tickets_are_paginated_with_10_items_per_page(): void
    {
        $user = User::factory()->create();

        // Create 15 tickets
        Ticket::factory()->count(15)->create([
            'created_by' => $user->id,
            'category' => TicketCategory::BILLING,
        ]);

        $response = $this->actingAs($user)->get(route('tickets.index'));

        $response->assertStatus(200);
        $response->assertViewHas('tickets');

        $tickets = $response->viewData('tickets');

        $this->assertEquals(10, $tickets->perPage());
        $this->assertEquals(15, $tickets->total());
        $this->assertEquals(2, $tickets->lastPage());
        $this->assertCount(10, $tickets->items());
    }

    public function test_pagination_preserves_sorting_and_filtering_query_parameters(): void
    {
        $user = User::factory()->create();

        // Create 15 tickets matching filter
        Ticket::factory()->count(15)->create([
            'status' => TicketStatus::OPEN,
            'priority' => TicketPriority::HIGH,
            'created_by' => $user->id,
            'category' => TicketCategory::TECHNICAL_SUPPORT,
        ]);

        $response = $this->actingAs($user)->get(route('tickets.index', [
            'status' => TicketStatus::OPEN->value,
            'priority' => TicketPriority::HIGH->value,
            'sort' => 'priority',
            'direction' => 'asc',
        ]));

        $response->assertStatus(200);

        $tickets = $response->viewData('tickets');
        $links = (string) $tickets->links();

        $this->assertStringContainsString('status=' . TicketStatus::OPEN->value, $links);
        $this->assertStringContainsString('priority=' . TicketPriority::HIGH->value, $links);
        $this->assertStringContainsString('sort=priority', $links);
        $this->assertStringContainsString('direction=asc', $links);
        $this->assertStringContainsString('page=2', $links);
    }
}
