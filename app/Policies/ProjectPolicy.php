<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

final class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('admin') || $project->owner_id === $user->id) {
            return true;
        }

        return $project->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->hasRole('admin') || $project->owner_id === $user->id) {
            return true;
        }

        return $project->projectMembers()
            ->where('user_id', $user->id)
            ->where('is_leader', true)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole('admin') || $project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can manage members of the project.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
