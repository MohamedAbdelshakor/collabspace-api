<?php

declare(strict_types=1);

namespace App\Actions\Chats;

use App\Enums\ConversationType;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StartDirectConversationAction
{
    public function handle(User $initiator, User $recipient): Conversation
    {
        return DB::transaction(function () use ($initiator, $recipient): Conversation {
            $minId = min($initiator->id, $recipient->id);
            $maxId = max($initiator->id, $recipient->id);
            $directKey = "direct:{$minId}:{$maxId}";

            /** @var Conversation $conversation */
            $conversation = Conversation::firstOrCreate(
                ['direct_key' => $directKey],
                [
                    'type' => ConversationType::Direct,
                ]
            );

            // Sync participants with initial joined timestamp
            $conversation->participants()->syncWithoutDetaching([
                $initiator->id => ['joined_at' => now()],
                $recipient->id => ['joined_at' => now()],
            ]);

            return $conversation->load(['participants', 'lastMessage.user']);
        });
    }
}
