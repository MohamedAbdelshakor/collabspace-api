<?php

declare(strict_types=1);

namespace App\Actions\Meetings;

use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;

final class CreateMeetingNoteAction
{
    public function handle(Meeting $meeting, User $user, string $body): MeetingNote
    {
        /** @var MeetingNote $note */
        $note = MeetingNote::updateOrCreate(
            [
                'meeting_id' => $meeting->id,
                'user_id' => $user->id,
            ],
            [
                'body' => $body,
            ]
        );

        return $note->load('user');
    }
}
