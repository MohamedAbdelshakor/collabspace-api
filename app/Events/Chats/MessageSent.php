<?php

declare(strict_types=1);

namespace App\Events\Chats;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        /** @var Conversation $conversation */
        $conversation = $this->message->conversation;

        return [
            new PrivateChannel('conversations.'.$conversation->ulid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $user = $this->message->user;

        return [
            'id' => $this->message->ulid,
            'conversation_id' => $this->message->conversation?->ulid,
            'body' => $this->message->body,
            'sender' => [
                'id' => $user?->ulid,
                'name' => $user?->name,
                'avatar_url' => $user?->avatar_path,
            ],
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];
    }
}
