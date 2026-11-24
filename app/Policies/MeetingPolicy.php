<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

final class MeetingPolicy
{
    /**
     * Determine whether the user can view any meetings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the meeting.
     */
    public function view(User $user, Meeting $meeting): bool
    {
        if ($user->hasRole('admin') || $meeting->organizer_id === $user->id) {
            return true;
        }

        if ($meeting->attendees()->where('users.id', $user->id)->exists()) {
            return true;
        }

        return $meeting->project?->members()->where('users.id', $user->id)->exists() ?? false;
    }

    /**
     * Determine whether the user can create meetings.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the meeting.
     */
    public function update(User $user, Meeting $meeting): bool
    {
        return $user->hasRole('admin') || $meeting->organizer_id === $user->id;
    }

    /**
     * Determine whether the user can delete the meeting.
     */
    public function delete(User $user, Meeting $meeting): bool
    {
        return $user->hasRole('admin') || $meeting->organizer_id === $user->id;
    }

    /**
     * Determine whether the user can upload a recording for the meeting.
     */
    public function uploadRecording(User $user, Meeting $meeting): bool
    {
        return $this->update($user, $meeting);
    }

    /**
     * Determine whether the user can trigger an AI summary.
     */
    public function generateSummary(User $user, Meeting $meeting): bool
    {
        return $this->view($user, $meeting);
    }
}
