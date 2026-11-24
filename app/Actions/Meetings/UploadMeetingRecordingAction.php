<?php

declare(strict_types=1);

namespace App\Actions\Meetings;

use App\Enums\MeetingRecordingStatus;
use App\Models\Meeting;
use Illuminate\Http\UploadedFile;

final class UploadMeetingRecordingAction
{
    public function handle(Meeting $meeting, UploadedFile $file): void
    {
        $meeting->addMedia($file)->toMediaCollection('recording');

        $meeting->update([
            'recording_status' => MeetingRecordingStatus::Ready,
        ]);
    }
}
