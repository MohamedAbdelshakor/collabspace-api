<?php

declare(strict_types=1);

namespace App\Actions\Meetings;

use App\Enums\MeetingAttendeeResponse;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateMeetingAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Meeting $meeting, array $data): Meeting
    {
        return DB::transaction(function () use ($meeting, $data): Meeting {
            if (array_key_exists('attendee_ids', $data)) {
                $attendeeUlids = (array) $data['attendee_ids'];
                $attendeeIds = User::whereIn('ulid', $attendeeUlids)->pluck('id')->all();

                $sync = [];
                foreach ($attendeeIds as $id) {
                    $sync[$id] = ['response' => MeetingAttendeeResponse::Pending];
                }
                // Keep organizer
                $sync[$meeting->organizer_id] = ['response' => MeetingAttendeeResponse::Accepted];

                $meeting->attendees()->sync($sync);
                unset($data['attendee_ids']);
            }

            $meeting->update($data);

            return $meeting->refresh()->load(['organizer', 'attendees', 'project']);
        });
    }
}
