<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateTaskAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data): Task {
            if (array_key_exists('reviewer_id', $data)) {
                if (! empty($data['reviewer_id'])) {
                    /** @var User|null $reviewer */
                    $reviewer = User::where('ulid', $data['reviewer_id'])->first();
                    $data['reviewer_id'] = $reviewer?->id;
                } else {
                    $data['reviewer_id'] = null;
                }
            }

            if (array_key_exists('assignee_ids', $data)) {
                $assigneeUlids = (array) $data['assignee_ids'];
                $assigneeIds = User::whereIn('ulid', $assigneeUlids)->pluck('id')->all();
                $syncData = [];
                foreach ($assigneeIds as $id) {
                    $syncData[$id] = ['assigned_at' => now()];
                }
                $task->assignees()->sync($syncData);
                unset($data['assignee_ids']);
            }

            $task->update($data);

            return $task->refresh()->load(['project', 'creator', 'reviewer', 'assignees']);
        });
    }
}
