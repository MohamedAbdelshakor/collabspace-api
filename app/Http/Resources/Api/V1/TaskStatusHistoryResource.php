<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\TaskStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TaskStatusHistory
 */
final class TaskStatusHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $createdAt = $this->created_at;

        return [
            'id' => $this->id,
            'from_status' => $this->from_status instanceof \BackedEnum ? $this->from_status->value : (string) $this->from_status,
            'to_status' => $this->to_status instanceof \BackedEnum ? $this->to_status->value : (string) $this->to_status,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $createdAt instanceof \DateTimeInterface ? $createdAt->format(\DateTimeInterface::ATOM) : (is_string($createdAt) ? $createdAt : null),
        ];
    }
}
