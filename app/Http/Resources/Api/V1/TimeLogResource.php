<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TimeLog
 */
final class TimeLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $loggedOn = $this->logged_on;

        return [
            'id' => $this->id,
            'minutes' => $this->minutes,
            'logged_on' => $loggedOn instanceof \DateTimeInterface ? $loggedOn->format('Y-m-d') : (is_string($loggedOn) ? $loggedOn : null),
            'note' => $this->note,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
