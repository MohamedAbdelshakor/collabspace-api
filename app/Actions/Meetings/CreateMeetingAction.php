<?php

declare(strict_types=1);

namespace App\Actions\Meetings;

use App\Enums\MeetingAttendeeResponse;
use App\Enums\MeetingRecordingStatus;
use App\Enums\MeetingStatus;
use App\Models\Conversation;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateMeetingAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $organizer, array $data): Meeting
    {
        return DB::transaction(function () use ($organizer, $data): Meeting {
            $projectId = null;
            if (! empty($data['project_id'])) {
                /** @var Project|null $project */
                $project = Project::where('ulid', $data['project_id'])->first();
                $projectId = $project?->id;
            }

            $conversationId = null;
            if (! empty($data['conversation_id'])) {
                /** @var Conversation|null $conv */
                $conv = Conversation::where('ulid', $data['conversation_id'])->first();
                $conversationId = $conv?->id;
            }

            $meetingUrl = $data['meeting_url'] ?? null;
            if (empty($meetingUrl)) {
                $code = Str::random(12);
                $meetingUrl = "https://meet.jit.si/collabspace-{$code}";
            }

            /** @var Meeting $meeting */
            $meeting = Meeting::create([
                'project_id' => $projectId,
                'organizer_id' => $organizer->id,
                'conversation_id' => $conversationId,
                'title' => (string) $data['title'],
                'description' => $data['description'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'meeting_url' => $meetingUrl,
                'status' => $data['status'] ?? MeetingStatus::Scheduled,
                'recording_status' => MeetingRecordingStatus::None,
            ]);

            // Automatically attach organizer as accepted
            $attendeeSync = [
                $organizer->id => ['response' => MeetingAttendeeResponse::Accepted],
            ];

            // Attach invited attendees as pending
            if (! empty($data['attendee_ids']) && is_array($data['attendee_ids'])) {
                $attendeeIds = User::whereIn('ulid', $data['attendee_ids'])
                    ->where('id', '!=', $organizer->id)
                    ->pluck('id')
                    ->all();

                foreach ($attendeeIds as $id) {
                    $attendeeSync[$id] = ['response' => MeetingAttendeeResponse::Pending];
                }
            }

            $meeting->attendees()->sync($attendeeSync);

            return $meeting->load(['organizer', 'attendees', 'project']);
        });
    }
}
