<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PolishReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_polish_draft_reply(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Thank you for reaching out. We are currently addressing the issue and will provide an update shortly.']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->post(route('tickets.replies.polish'), [
            'body' => 'sorry for delay we are fixing it now',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'polished_reply' => 'Thank you for reaching out. We are currently addressing the issue and will provide an update shortly.',
        ]);
    }

    public function test_polish_reply_fails_validation_when_body_is_empty(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->post(route('tickets.replies.polish'), [
            'body' => '',
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_polish_reply_handles_ai_process_failure_gracefully(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Rate limit exceeded']
            ], 429),
        ]);

        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->post(route('tickets.replies.polish'), [
            'body' => 'sorry for delay we are fixing it now',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Unable to polish reply at this time due to AI service limits. Please try again in a moment.',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_polish_endpoint(): void
    {
        $response = $this->post(route('tickets.replies.polish'), [
            'body' => 'some draft message',
        ]);

        $response->assertRedirect(route('login'));
    }
}
