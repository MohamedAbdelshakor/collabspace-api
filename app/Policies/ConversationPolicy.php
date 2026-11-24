<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

final class ConversationPolicy
{
    /**
     * Determine whether the user can view any conversations.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $conversation->participants()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can send messages in the conversation.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine whether the user can mark messages as read.
     */
    public function read(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
