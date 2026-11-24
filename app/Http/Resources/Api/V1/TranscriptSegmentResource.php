<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\TranscriptSegment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TranscriptSegment
 */
final class TranscriptSegmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $seconds = (int) $this->start_second;
        $formattedTimestamp = gmdate('i:s', $seconds);

        return [
            'id' => $this->id,
            'start_second' => $seconds,
            'timestamp' => $formattedTimestamp,
            'language' => $this->language,
            'speaker' => new UserResource($this->whenLoaded('speaker')),
            'text' => $this->text,
        ];
    }
}
