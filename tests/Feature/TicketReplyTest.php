<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_reply_to_ticket_and_sender_type_is_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $creator = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($agent)->post(route('tickets.replies.store', $ticket), [
            'body' => 'Thank you for reaching out. We are investigating this issue.',
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $response->assertSessionHas('success', 'Reply submitted successfully.');

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'body' => 'Thank you for reaching out. We are investigating this issue.',
            'sender_type' => 'agent',
        ]);

        $reply = TicketReply::first();
        $this->assertEquals('agent', $reply->senderType);
        $this->assertEquals('agent', $reply->sender_type);

        $showResponse = $this->actingAs($agent)->get(route('tickets.show', $ticket));
        $showResponse->assertSee('Thank you for reaching out. We are investigating this issue.');
        $showResponse->assertSee('Agent Response');
    }

    public function test_creator_can_reply_to_ticket_and_sender_type_is_customer(): void
    {
        $creator = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $creator->id]);

        // Non-agent / creator replying
        $customer = User::factory()->create();
        $customer->forceFill(['role' => 'customer']);

        // Set creator as customer
        $ticket->update(['created_by' => $customer->id]);

        $response = $this->actingAs($customer)->post(route('tickets.replies.store', $ticket), [
            'body' => 'Here are additional details regarding my ticket.',
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'sender_type' => 'customer',
        ]);

        $reply = TicketReply::first();
        $this->assertEquals('customer', $reply->senderType);
        $this->assertEquals('customer', $reply->sender_type);
    }

    public function test_reply_validation_fails_when_body_is_empty(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        $response = $this->actingAs($agent)->post(route('tickets.replies.store', $ticket), [
            'body' => '',
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_unauthorized_user_cannot_reply_to_ticket(): void
    {
        $creator = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['created_by' => $creator->id]);

        $unauthorizedUser = new User();
        $unauthorizedUser->forceFill(['id' => 9999, 'role' => 'customer']);

        $response = $this->actingAs($unauthorizedUser)->post(route('tickets.replies.store', $ticket), [
            'body' => 'Attempting unauthorized reply',
        ]);

        $response->assertStatus(403);
    }
}
