<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class MeetingAttendeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Pivot|null $pivot */
        $pivot = $this->resource->getAttribute('pivot');
        $response = $pivot?->getAttribute('response');
        $responseValue = $response instanceof \BackedEnum ? $response->value : (string) ($response ?? 'pending');

        return [
            'id' => $this->ulid,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_path,
            'job_title' => $this->job_title,
            'response_status' => $responseValue,
            'joined_at' => $pivot?->getAttribute('joined_at'),
        ];
    }
}
