<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
final class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = $this->start_date;
        $dueDate = $this->due_date;
        $completedAt = $this->completed_at;

        return [
            'id' => $this->ulid,
            'project_id' => $this->relationLoaded('project') ? $this->project?->ulid : null,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'priority' => $this->priority instanceof \BackedEnum ? $this->priority->value : (string) $this->priority,
            'progress' => $this->progress,
            'position' => $this->position,
            'start_date' => $startDate instanceof \DateTimeInterface ? $startDate->format('Y-m-d') : (is_string($startDate) ? $startDate : null),
            'due_date' => $dueDate instanceof \DateTimeInterface ? $dueDate->format('Y-m-d') : (is_string($dueDate) ? $dueDate : null),
            'completed_at' => $completedAt instanceof \DateTimeInterface ? $completedAt->format(\DateTimeInterface::ATOM) : (is_string($completedAt) ? $completedAt : null),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'assignees' => UserResource::collection($this->whenLoaded('assignees')),
            'time_logged_minutes' => $this->whenLoaded('timeLogs', fn () => $this->timeLogs->sum('minutes')),
            'comments_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
