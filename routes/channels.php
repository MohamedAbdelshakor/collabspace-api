<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversations.{conversationUlid}', function (User $user, string $conversationUlid): bool {
    /** @var Conversation|null $conversation */
    $conversation = Conversation::where('ulid', $conversationUlid)->first();

    if (! $conversation) {
        return false;
    }

    return $conversation->participants()->where('users.id', $user->id)->exists();
});

Broadcast::channel('users.{userUlid}', function (User $user, string $userUlid): bool {
    return $user->ulid === $userUlid;
});
