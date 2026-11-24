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

        return [
            'id' => $this->ulid,
            'project_id' => $this->project?->ulid,
            'conversation_id' => $this->conversation_id,
            'title' => $this->title,
            'description' => $this->description,
            'starts_at' => $startsAt instanceof \DateTimeInterface ? $startsAt->format(\DateTimeInterface::ATOM) : (is_string($startsAt) ? $startsAt : null),
            'ends_at' => $endsAt instanceof \DateTimeInterface ? $endsAt->format(\DateTimeInterface::ATOM) : (is_string($endsAt) ? $endsAt : null),
            'meeting_url' => $this->meeting_url,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'organizer' => new UserResource($this->whenLoaded('organizer')),
            'attendees' => UserResource::collection($this->whenLoaded('attendees')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
