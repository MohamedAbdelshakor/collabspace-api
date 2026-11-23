<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TimeLog;
use App\Models\User;

final class TimeLogPolicy
{
    /**
     * Determine whether the user can delete the time log.
     */
    public function delete(User $user, TimeLog $timeLog): bool
    {
        return $user->hasRole('admin') || $timeLog->user_id === $user->id;
    }
}
