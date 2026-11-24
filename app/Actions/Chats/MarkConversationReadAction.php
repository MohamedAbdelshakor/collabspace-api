<?php

declare(strict_types=1);

namespace App\Actions\Chats;

use App\Events\Chats\MessageRead;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

final class MarkConversationReadAction
{
    public function handle(Conversation $conversation, User $user, ?string $messageUlid = null): void
    {
        $targetMessageId = null;

        if ($messageUlid) {
            /** @var Message|null $msg */
            $msg = Message::where('conversation_id', $conversation->id)
                ->where('ulid', $messageUlid)
                ->first();
            $targetMessageId = $msg?->id;
        }

        if (! $targetMessageId) {
            $targetMessageId = $conversation->last_message_id;
        }

        if ($targetMessageId) {
            $conversation->participants()->updateExistingPivot($user->id, [
                'last_read_message_id' => $targetMessageId,
            ]);

            MessageRead::dispatch($conversation, $user, $targetMessageId);
        }
    }
}
