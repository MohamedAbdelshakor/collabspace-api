<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Exceptions\TaskReviewerRequiredException;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class ReorderTasksAction
{
    /**
     * @param  list<array{id: string, status: string|TaskStatus, position: int}>  $taskOrderItems
     */
    public function handle(Project $project, array $taskOrderItems): void
    {
        DB::transaction(function () use ($project, $taskOrderItems): void {
            $taskUlids = array_column($taskOrderItems, 'id');
            /** @var Collection<string, Task> $tasks */
            $tasks = Task::where('project_id', $project->id)
                ->whereIn('ulid', $taskUlids)
                ->get()
                ->keyBy('ulid');

            foreach ($taskOrderItems as $item) {
                $ulid = (string) $item['id'];
                if (! isset($tasks[$ulid])) {
                    continue;
                }

                $task = $tasks[$ulid];
                $targetStatus = $item['status'] instanceof TaskStatus
                    ? $item['status']
                    : TaskStatus::from((string) $item['status']);

                // Invariant: cannot move to InReview without reviewer
                if ($targetStatus === TaskStatus::InReview && empty($task->reviewer_id)) {
                    throw new TaskReviewerRequiredException;
                }

                $task->status = $targetStatus;
                $task->position = (int) $item['position'];
                $task->save();
            }
        });
    }
}
