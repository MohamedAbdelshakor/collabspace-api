<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateTaskAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Project $project, User $creator, array $data): Task
    {
        return DB::transaction(function () use ($project, $creator, $data): Task {
            $status = isset($data['status'])
                ? ($data['status'] instanceof TaskStatus ? $data['status'] : TaskStatus::from((string) $data['status']))
                : TaskStatus::Todo;

            $maxPosition = (int) (Task::where('project_id', $project->id)
                ->where('status', $status)
                ->max('position') ?? -1);

            $reviewerId = null;
            if (! empty($data['reviewer_id'])) {
                /** @var User|null $reviewer */
                $reviewer = User::where('ulid', $data['reviewer_id'])->first();
                $reviewerId = $reviewer?->id;
            }

            /** @var Task $task */
            $task = Task::create([
                'project_id' => $project->id,
                'created_by' => $creator->id,
                'reviewer_id' => $reviewerId,
                'title' => (string) $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $status,
                'priority' => $data['priority'],
                'position' => $maxPosition + 1,
                'progress' => 0,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            if (! empty($data['assignee_ids']) && is_array($data['assignee_ids'])) {
                $assigneeIds = User::whereIn('ulid', $data['assignee_ids'])->pluck('id')->all();
                $attachData = [];
                foreach ($assigneeIds as $id) {
                    $attachData[$id] = ['assigned_at' => now()];
                }
                $task->assignees()->attach($attachData);
            }

            return $task->load(['project', 'creator', 'reviewer', 'assignees']);
        });
    }
}
