<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Ai;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CollabspaceAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_or_start_ai_conversation_with_greeting(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/ai/conversation');

        $response->assertOk()
            ->assertJsonPath('data.title', 'Collabspace AI')
            ->assertJsonCount(1, 'data.messages')
            ->assertJsonPath('data.messages.0.role', 'assistant')
            ->assertJsonPath('data.messages.0.content', "Hello! I'm your personal AI Assistant, How can i help you?");
    }

    public function test_user_can_send_message_to_ai_assistant_and_receives_response(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/ai/messages', [
            'message' => 'What tasks do I need to complete this week?',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user_message' => [
                        'id',
                        'role',
                        'content',
                        'created_at',
                    ],
                    'assistant_message' => [
                        'id',
                        'role',
                        'sender',
                        'content',
                        'actions',
                        'created_at',
                    ],
                ],
            ])
            ->assertJsonPath('data.user_message.role', 'user')
            ->assertJsonPath('data.assistant_message.role', 'assistant')
            ->assertJsonPath('data.assistant_message.sender', 'CB');

        $this->assertNotEmpty($response->json('data.assistant_message.content'));
    }

    public function test_user_can_send_message_with_external_link_and_extracts_preview(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/ai/messages', [
            'message' => 'Please analyze this page https://www.externallink.com for project guidelines',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user_message.link_preview.url', 'https://www.externallink.com')
            ->assertJsonPath('data.user_message.link_preview.title', 'External Link Title');
    }

    public function test_user_can_submit_feedback_on_ai_message(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $sendResponse = $this->postJson('/api/v1/ai/messages', [
            'message' => 'Give me project status updates for SepetGo',
        ]);

        $assistantMessageId = $sendResponse->json('data.assistant_message.id');

        $feedbackResponse = $this->postJson("/api/v1/ai/messages/{$assistantMessageId}/feedback", [
            'rating' => 'down',
            'comment' => 'Please provide more details on upcoming milestones.',
        ]);

        $feedbackResponse->assertOk()
            ->assertJsonPath('data.feedback.rating', 'down')
            ->assertJsonPath('data.feedback.comment', 'Please provide more details on upcoming milestones.');
    }

    public function test_user_can_reset_ai_conversation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Send a message
        $this->postJson('/api/v1/ai/messages', [
            'message' => 'Hello AI, remember this test message.',
        ]);

        // Reset conversation
        $resetResponse = $this->postJson('/api/v1/ai/reset');

        $resetResponse->assertOk()
            ->assertJsonCount(1, 'data.messages')
            ->assertJsonPath('data.messages.0.content', "Hello! I'm your personal AI Assistant, How can i help you?");
    }
}
