<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Exceptions\TaskReviewerRequiredException;
use App\Models\Task;

final class ChangeTaskStatusAction
{
    public function handle(Task $task, TaskStatus $status): Task
    {
        // Business Rule: moving to InReview requires a reviewer assigned
        if ($status === TaskStatus::InReview && empty($task->reviewer_id)) {
            throw new TaskReviewerRequiredException;
        }

        $task->update(['status' => $status]);

        return $task->refresh()->load(['project', 'creator', 'reviewer', 'assignees']);
    }
}
