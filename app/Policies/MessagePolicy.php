<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

final class MessagePolicy
{
    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->hasRole('admin') || $message->user_id === $user->id;
    }
}
