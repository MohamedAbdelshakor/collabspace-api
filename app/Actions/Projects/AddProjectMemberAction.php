<?php

declare(strict_types=1);

namespace App\Actions\Projects;

use App\Models\Conversation;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class AddProjectMemberAction
{
    public function handle(
        Project $project,
        User $user,
        ?Department $department = null,
        ?string $roleTitle = null,
        bool $isLeader = false
    ): void {
        DB::transaction(function () use ($project, $user, $department, $roleTitle, $isLeader): void {
            $project->members()->syncWithoutDetaching([
                $user->id => [
                    'department_id' => $department?->id,
                    'role_title' => $roleTitle ?? $user->job_title ?? 'Member',
                    'is_leader' => $isLeader,
                    'joined_at' => now(),
                ],
            ]);

            // Sync user into all project conversations (public channels)
            /** @var Collection<int, Conversation> $conversations */
            $conversations = $project->conversations()->get();
            foreach ($conversations as $conversation) {
                $conversation->participants()->syncWithoutDetaching([
                    $user->id => ['joined_at' => now()],
                ]);
            }
        });
    }
}
