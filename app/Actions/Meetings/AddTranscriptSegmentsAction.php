<?php

declare(strict_types=1);

namespace App\Actions\Meetings;

use App\Models\Meeting;
use App\Models\TranscriptSegment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class AddTranscriptSegmentsAction
{
    /**
     * @param  list<array{speaker_id?: string|null, start_second: int, text: string, language?: string|null}>  $segments
     */
    public function handle(Meeting $meeting, array $segments): void
    {
        DB::transaction(function () use ($meeting, $segments): void {
            $speakerUlids = array_filter(array_column($segments, 'speaker_id'));
            /** @var Collection<string, User> $speakers */
            $speakers = User::whereIn('ulid', $speakerUlids)->get()->keyBy('ulid');

            foreach ($segments as $segment) {
                $speakerId = null;
                if (! empty($segment['speaker_id']) && isset($speakers[$segment['speaker_id']])) {
                    $speakerId = $speakers[$segment['speaker_id']]->id;
                }

                TranscriptSegment::create([
                    'meeting_id' => $meeting->id,
                    'speaker_id' => $speakerId,
                    'start_second' => (int) $segment['start_second'],
                    'text' => (string) $segment['text'],
                    'language' => $segment['language'] ?? 'en',
                ]);
            }
        });
    }
}
