<?php

declare(strict_types=1);

namespace App\Actions\Comments;

use App\Models\Comment;

final class DeleteCommentAction
{
    public function handle(Comment $comment): void
    {
        $comment->delete();
    }
}
