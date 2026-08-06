<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TicketSummarizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_trigger_summarize_and_stores_in_database(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "• Core Issue: Customer reported severe network latency in US-East.\n• Progress: NOC rerouted traffic and replaced crashed worker node 10.0.4.88.\n• Resolution: Verified 38ms average response time across 10,000 requests. Ticket resolved."]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        TicketReply::factory()->count(3)->create(['ticket_id' => $ticket->id]);

        $response = $this->actingAs($agent)->post(route('tickets.summarize', $ticket));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $summaryText = $response->json('summary');
        $this->assertStringContainsString('US-East', $summaryText);

        // Verify summary is stored in the database
        $ticket->refresh();
        $this->assertEquals($summaryText, $ticket->summary);
    }

    public function test_summarize_handles_ai_process_failure_gracefully(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Rate limit exceeded']
            ], 429),
        ]);

        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id, 'summary' => null]);

        $response = $this->actingAs($agent)->post(route('tickets.summarize', $ticket));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Unable to generate AI summary at this time. Please try again in a few moments.',
        ]);
    }

    public function test_summarize_returns_previous_summary_if_ai_fails_and_summary_exists(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Rate limit exceeded']
            ], 429),
        ]);

        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create([
            'created_by' => $agent->id,
            'summary' => 'Previous saved summary content.',
        ]);

        $response = $this->actingAs($agent)->post(route('tickets.summarize', $ticket));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => 'Previous saved summary content.',
        ]);
    }

    public function test_unauthorized_user_cannot_summarize_ticket(): void
    {
        $creator = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $creator->id]);

        $unauthorizedUser = new User();
        $unauthorizedUser->forceFill(['id' => 9999, 'role' => 'customer']);

        $response = $this->actingAs($unauthorizedUser)->post(route('tickets.summarize', $ticket));

        $response->assertStatus(403);
    }
}
