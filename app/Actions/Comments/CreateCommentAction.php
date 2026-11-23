<?php

declare(strict_types=1);

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

final class CreateCommentAction
{
    public function handle(Task $task, User $user, string $body): Comment
    {
        /** @var Comment $comment */
        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'body' => $body,
        ]);

        return $comment->load('user');
    }
}
