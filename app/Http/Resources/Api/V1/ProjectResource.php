<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
final class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = $this->start_date;
        $deadline = $this->deadline;
        $completedAt = $this->completed_at;

        return [
            'id' => $this->ulid,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type instanceof \BackedEnum ? $this->type->value : (string) $this->type,
            'priority' => $this->priority instanceof \BackedEnum ? $this->priority->value : (string) $this->priority,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'progress' => $this->progress,
            'start_date' => $startDate instanceof \DateTimeInterface ? $startDate->format('Y-m-d') : (is_string($startDate) ? $startDate : null),
            'deadline' => $deadline instanceof \DateTimeInterface ? $deadline->format('Y-m-d') : (is_string($deadline) ? $deadline : null),
            'completed_at' => $completedAt instanceof \DateTimeInterface ? $completedAt->format(\DateTimeInterface::ATOM) : (is_string($completedAt) ? $completedAt : null),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'members_count' => $this->whenCounted('members'),
            'members' => ProjectMemberResource::collection($this->whenLoaded('members')),
            'tasks_count' => $this->whenCounted('tasks'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
