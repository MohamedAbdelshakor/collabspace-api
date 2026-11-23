<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TaskStatus;
use App\Exceptions\TaskReviewerRequiredException;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatusHistory;
use Illuminate\Support\Facades\Auth;

final class TaskObserver
{
    /**
     * Handle the Task "saving" event.
     */
    public function saving(Task $task): void
    {
        // Business Rule 1: A task cannot move to In Review without a reviewer
        if ($task->isDirty('status') && $task->status === TaskStatus::InReview && empty($task->reviewer_id)) {
            throw new TaskReviewerRequiredException;
        }

        // Auto-manage completed_at timestamp
        if ($task->isDirty('status')) {
            if ($task->status === TaskStatus::Completed && $task->completed_at === null) {
                $task->completed_at = now();
            } elseif ($task->status !== TaskStatus::Completed && $task->completed_at !== null) {
                $task->completed_at = null;
            }
        }
    }

    /**
     * Handle the Task "saved" event.
     */
    public function saved(Task $task): void
    {
        if ($task->wasChanged('status')) {
            $fromRaw = $task->getOriginal('status');
            $fromStatus = $fromRaw instanceof TaskStatus
                ? $fromRaw
                : (is_string($fromRaw) ? TaskStatus::tryFrom($fromRaw) : null);

            TaskStatusHistory::create([
                'task_id' => $task->id,
                'user_id' => Auth::id() ?? $task->created_by,
                'from_status' => $fromStatus ?? TaskStatus::Todo,
                'to_status' => $task->status,
                'created_at' => now(),
            ]);

            $total = Task::where('project_id', $task->project_id)->count();
            $done = Task::where('project_id', $task->project_id)->where('status', TaskStatus::Completed)->count();
            $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;

            Project::where('id', $task->project_id)->update([
                'progress' => $progress,
                'completed_at' => $progress === 100 ? now() : null,
            ]);
        }
    }
}
