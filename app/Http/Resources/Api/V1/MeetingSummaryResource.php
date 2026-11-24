<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\MeetingSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MeetingSummary
 */
final class MeetingSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $generatedAt = $this->generated_at;

        return [
            'id' => $this->id,
            'language' => $this->language,
            'key_points' => $this->key_points ?? [],
            'action_items' => $this->action_items ?? [],
            'generated_at' => $generatedAt instanceof \DateTimeInterface ? $generatedAt->format(\DateTimeInterface::ATOM) : (is_string($generatedAt) ? $generatedAt : null),
        ];
    }
}
