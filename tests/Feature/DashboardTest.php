<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Jobs\ProcessIncomingTicketJob;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AiAgentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_agent_seeder_creates_ai_agent_user(): void
    {
        $this->seed(AiAgentSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'AI',
            'email' => 'ai@helpdesk.com',
            'role' => 'agent',
        ]);
    }

    public function test_webhook_ticket_creation_automatically_assigns_to_ai_agent(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $this->seed(AiAgentSeeder::class);
        $aiAgent = User::where('email', 'ai@helpdesk.com')->firstOrFail();

        $payload = [
            'subject' => 'How can I reset my password?',
            'description' => 'I forgot my account password.',
            'customer_email' => 'john@example.com',
            'customer_name' => 'John Customer',
        ];

        $response = $this->postJson('/api/webhooks/tickets', $payload);

        $response->assertStatus(201);

        $ticket = Ticket::where('subject', 'How can I reset my password?')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals(TicketStatus::NEW, $ticket->status);
        $this->assertEquals($aiAgent->id, $ticket->assigned_to);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessIncomingTicketJob::class);
    }

    public function test_auto_resolvable_ticket_keeps_ai_agent_assignment_and_sets_resolved_at(): void
    {
        $this->seed(AiAgentSeeder::class);
        $aiAgent = User::where('email', 'ai@helpdesk.com')->firstOrFail();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'can_answer' => true,
                                    'solution' => 'Click Forgot Password on the login page.'
                                ])]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $customer = User::factory()->create(['name' => 'John Doe']);
        $ticket = Ticket::factory()->create([
            'subject' => 'How do I reset my password?',
            'status' => TicketStatus::NEW,
            'assigned_to' => $aiAgent->id,
            'created_by' => $customer->id,
        ]);

        $job = new ProcessIncomingTicketJob($ticket->id);
        $job->handle();

        $ticket->refresh();

        $this->assertEquals(TicketStatus::RESOLVED, $ticket->status);
        $this->assertEquals($aiAgent->id, $ticket->assigned_to);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertCount(1, $ticket->replies);
    }

    public function test_unresolvable_ticket_removes_ai_agent_assignment_and_sets_status_open(): void
    {
        $this->seed(AiAgentSeeder::class);
        $aiAgent = User::where('email', 'ai@helpdesk.com')->firstOrFail();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'can_answer' => false,
                                    'solution' => ''
                                ])]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $customer = User::factory()->create(['name' => 'Alice Smith']);
        $ticket = Ticket::factory()->create([
            'subject' => 'Can you provide a discount coupon?',
            'status' => TicketStatus::NEW,
            'assigned_to' => $aiAgent->id,
            'created_by' => $customer->id,
        ]);

        $job = new ProcessIncomingTicketJob($ticket->id);
        $job->handle();

        $ticket->refresh();

        $this->assertEquals(TicketStatus::OPEN, $ticket->status);
        $this->assertNull($ticket->assigned_to);
        $this->assertCount(0, $ticket->replies);
    }

    public function test_dashboard_page_loads_and_displays_metrics_and_chart(): void
    {
        $this->seed(AiAgentSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);

        // Create sample tickets
        Ticket::factory()->count(5)->create(['status' => TicketStatus::OPEN]);
        Ticket::factory()->count(3)->create(['status' => TicketStatus::RESOLVED, 'assigned_to' => User::where('email', 'ai@helpdesk.com')->first()->id]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Tickets');
        $response->assertSee('Open Tickets');
        $response->assertSee('AI Resolved');
        $response->assertSee('ticketsTrendChart');
    }
}
