<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

final class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        /** @var Project $project */
        $project = $task->project;

        return $user->can('view', $project);
    }

    /**
     * Determine whether the user can create tasks for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->can('view', $project);
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        if ($user->hasRole('admin') || $task->created_by === $user->id || $task->reviewer_id === $user->id) {
            return true;
        }

        /** @var Project $project */
        $project = $task->project;
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        if ($user->hasRole('admin') || $task->created_by === $user->id) {
            return true;
        }

        /** @var Project $project */
        $project = $task->project;

        return $project->owner_id === $user->id;
    }
}
