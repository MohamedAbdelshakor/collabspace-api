<?php

declare(strict_types=1);

namespace App\Actions\Meetings;

use App\Enums\MeetingAttendeeResponse;
use App\Models\Meeting;
use App\Models\User;

final class RsvpMeetingAction
{
    public function handle(Meeting $meeting, User $user, MeetingAttendeeResponse $response): void
    {
        $meeting->attendees()->syncWithoutDetaching([
            $user->id => [
                'response' => $response,
                'joined_at' => $response === MeetingAttendeeResponse::Accepted ? now() : null,
            ],
        ]);
    }
}
