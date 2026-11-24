<?php

declare(strict_types=1);

namespace App\Actions\Chats;

use App\Enums\MeetingRecordingStatus;
use App\Enums\MeetingStatus;
use App\Models\Conversation;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateInstantMeetingAction
{
    public function handle(Conversation $conversation, User $creator, ?string $title = null): Meeting
    {
        return DB::transaction(function () use ($conversation, $creator, $title): Meeting {
            $meetingTitle = $title ?? ('Instant Meeting - '.($conversation->name ?? 'Chat'));
            $roomCode = Str::random(12);
            $appUrl = (string) config('app.frontend_url', config('app.url'));
            $meetingUrl = "{$appUrl}/meet/{$roomCode}";

            /** @var Meeting $meeting */
            $meeting = Meeting::create([
                'project_id' => $conversation->project_id,
                'organizer_id' => $creator->id,
                'conversation_id' => $conversation->id,
                'title' => $meetingTitle,
                'description' => 'Instant meeting started from chat conversation.',
                'starts_at' => now(),
                'ends_at' => now()->addMinutes(45),
                'meeting_url' => $meetingUrl,
                'status' => MeetingStatus::Live,
                'recording_status' => MeetingRecordingStatus::None,
            ]);

            // Add all conversation participants as attendees
            $participantIds = $conversation->participants()->pluck('users.id')->all();
            if ($participantIds !== []) {
                $meeting->attendees()->syncWithoutDetaching($participantIds);
            }

            return $meeting->load(['organizer', 'attendees']);
        });
    }
}
