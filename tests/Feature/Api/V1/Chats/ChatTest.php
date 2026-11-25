<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Chats;

use App\Enums\ConversationType;
use App\Enums\MeetingStatus;
use App\Events\Chats\MessageRead;
use App\Events\Chats\MessageSent;
use App\Jobs\FetchLinkPreviewJob;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_direct_conversation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Sanctum::actingAs($userA);

        $response = $this->postJson('/api/v1/conversations/direct', [
            'recipient_id' => $userB->ulid,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.type', ConversationType::Direct->value)
            ->assertJsonPath('data.display_title', $userB->name);

        $this->assertDatabaseHas('conversations', [
            'type' => ConversationType::Direct->value,
        ]);

        // Repeat call is idempotent (returns existing conversation)
        $repeatResponse = $this->postJson('/api/v1/conversations/direct', [
            'recipient_id' => $userB->ulid,
        ]);

        $repeatResponse->assertOk()
            ->assertJsonPath('data.id', $response->json('data.id'));
    }

    public function test_user_can_list_conversations_with_unread_count(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversation = Conversation::factory()->create(['type' => ConversationType::Direct]);
        $conversation->participants()->attach([
            $userA->id => ['last_read_message_id' => null],
            $userB->id => ['last_read_message_id' => null],
        ]);

        // User B sends 2 messages
        $msg1 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $userB->id,
            'body' => 'First message',
        ]);
        $msg2 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $userB->id,
            'body' => 'Second message',
        ]);

        $conversation->update([
            'last_message_id' => $msg2->id,
            'last_message_at' => now(),
        ]);

        Sanctum::actingAs($userA);

        $response = $this->getJson('/api/v1/conversations');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.unread_count', 2)
            ->assertJsonPath('data.0.last_message.body', 'Second message');
    }

    public function test_user_can_send_message_and_broadcasts_realtime_event(): void
    {
        Event::fake([MessageSent::class]);
        Queue::fake([FetchLinkPreviewJob::class]);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([$userA->id, $userB->id]);

        Sanctum::actingAs($userA);

        $response = $this->postJson("/api/v1/conversations/{$conversation->ulid}/messages", [
            'body' => 'Check the link at https://github.com/MohamedAbdelshakor/collabspace-api for updates.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'Check the link at https://github.com/MohamedAbdelshakor/collabspace-api for updates.')
            ->assertJsonPath('data.sender.id', $userA->ulid);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $userA->id,
        ]);

        Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($conversation, $userA): bool {
            return $event->message->conversation_id === $conversation->id
                && $event->message->user_id === $userA->id;
        });

        Queue::assertPushed(FetchLinkPreviewJob::class);
    }

    public function test_user_can_reply_to_message_in_thread(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([$userA->id, $userB->id]);

        $parent = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $userA->id,
            'body' => 'Initial inquiry',
            'replies_count' => 0,
        ]);

        Sanctum::actingAs($userB);

        $response = $this->postJson("/api/v1/conversations/{$conversation->ulid}/messages", [
            'body' => 'Thread reply',
            'parent_id' => $parent->ulid,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.parent_id', $parent->ulid);

        $this->assertEquals(1, $parent->fresh()->replies_count);
    }

    public function test_user_can_mark_conversation_as_read(): void
    {
        Event::fake([MessageRead::class]);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([
            $userA->id => ['last_read_message_id' => null],
            $userB->id => ['last_read_message_id' => null],
        ]);

        $msg = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $userB->id,
        ]);

        $conversation->update(['last_message_id' => $msg->id]);

        Sanctum::actingAs($userA);

        $response = $this->postJson("/api/v1/conversations/{$conversation->ulid}/read", [
            'message_id' => $msg->ulid,
        ]);

        $response->assertOk();

        Event::assertDispatched(MessageRead::class);
    }

    public function test_sender_can_delete_own_message(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([$userA->id, $userB->id]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $userA->id,
        ]);

        // User B cannot delete User A's message
        Sanctum::actingAs($userB);
        $this->deleteJson("/api/v1/messages/{$message->ulid}")
            ->assertForbidden();

        // User A can delete own message
        Sanctum::actingAs($userA);
        $this->deleteJson("/api/v1/messages/{$message->ulid}")
            ->assertOk();

        $this->assertSoftDeleted('messages', ['id' => $message->id]);
    }

    public function test_user_can_start_instant_meeting_from_chat(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversation = Conversation::factory()->create(['name' => 'Design Sync']);
        $conversation->participants()->attach([$userA->id, $userB->id]);

        Sanctum::actingAs($userA);

        $response = $this->postJson("/api/v1/conversations/{$conversation->ulid}/instant-meeting", [
            'title' => 'Quick Design Catchup',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Quick Design Catchup')
            ->assertJsonPath('data.status', MeetingStatus::Live->value);

        $this->assertDatabaseHas('meetings', [
            'conversation_id' => $conversation->id,
            'organizer_id' => $userA->id,
            'title' => 'Quick Design Catchup',
        ]);
    }

    public function test_stranger_cannot_access_or_send_in_conversation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $stranger = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([$userA->id, $userB->id]);

        Sanctum::actingAs($stranger);

        $this->getJson("/api/v1/conversations/{$conversation->ulid}")
            ->assertForbidden();

        $this->postJson("/api/v1/conversations/{$conversation->ulid}/messages", ['body' => 'Intruder'])
            ->assertForbidden();
    }

    public function test_participant_can_authenticate_reverb_private_channel_and_stranger_cannot(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-reverb-key',
            'broadcasting.connections.reverb.secret' => 'test-reverb-secret',
            'broadcasting.connections.reverb.app_id' => '123456',
        ]);
        Broadcast::purge('reverb');
        require base_path('routes/channels.php');

        $userA = User::factory()->create();
        $stranger = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([$userA->id]);

        // Participant succeeds and receives signed Reverb auth token
        Sanctum::actingAs($userA);
        $this->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '123456.789012',
            'channel_name' => "private-conversations.{$conversation->ulid}",
        ])->assertOk()
            ->assertJsonStructure(['auth']);

        // Stranger is forbidden
        Sanctum::actingAs($stranger);
        $this->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '123456.789012',
            'channel_name' => "private-conversations.{$conversation->ulid}",
        ])->assertForbidden();
    }
}
