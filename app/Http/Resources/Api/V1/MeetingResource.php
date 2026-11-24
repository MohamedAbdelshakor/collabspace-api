<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Meeting
 */
final class MeetingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startsAt = $this->starts_at;
        $endsAt = $this->ends_at;
        $recordingMedia = $this->getFirstMedia('recording');

        return [
            'id' => $this->ulid,
            'title' => $this->title,
            'description' => $this->description,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'project_id' => $this->relationLoaded('project') ? $this->project?->ulid : null,
            'conversation_id' => $this->relationLoaded('conversation') ? $this->conversation?->ulid : null,
            'starts_at' => $startsAt instanceof \DateTimeInterface ? $startsAt->format(\DateTimeInterface::ATOM) : (is_string($startsAt) ? $startsAt : null),
            'ends_at' => $endsAt instanceof \DateTimeInterface ? $endsAt->format(\DateTimeInterface::ATOM) : (is_string($endsAt) ? $endsAt : null),
            'meeting_url' => $this->meeting_url,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'recording_status' => $this->recording_status instanceof \BackedEnum ? $this->recording_status->value : (string) $this->recording_status,
            'recording_url' => $recordingMedia?->getUrl(),
            'organizer' => new UserResource($this->whenLoaded('organizer')),
            'attendees_count' => $this->whenCounted('attendees'),
            'attendees' => MeetingAttendeeResource::collection($this->whenLoaded('attendees')),
            'transcripts_count' => $this->whenCounted('transcriptSegments'),
            'notes_count' => $this->whenCounted('notes'),
            'comments_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
