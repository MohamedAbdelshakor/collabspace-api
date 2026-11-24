<?php

declare(strict_types=1);

namespace App\Actions\Meetings;

use App\Models\Meeting;
use App\Models\MeetingSummary;
use App\Services\AiMeetingSummarizer;

final class GenerateMeetingSummaryAction
{
    public function __construct(private readonly AiMeetingSummarizer $summarizer) {}

    public function handle(Meeting $meeting, string $language = 'en'): MeetingSummary
    {
        return $this->summarizer->summarize($meeting, $language);
    }
}
