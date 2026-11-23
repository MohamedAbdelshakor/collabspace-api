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
final class ProjectMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Pivot|null $pivot */
        $pivot = $this->resource->getAttribute('pivot');
        $joinedAt = $pivot?->getAttribute('joined_at');
        $joinedAtIso = $joinedAt instanceof \DateTimeInterface
            ? $joinedAt->format(\DateTimeInterface::ATOM)
            : (is_string($joinedAt) ? $joinedAt : null);

        return [
            'id' => $this->ulid,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_path,
            'job_title' => $this->job_title,
            'role_title' => $pivot?->getAttribute('role_title'),
            'is_leader' => (bool) $pivot?->getAttribute('is_leader'),
            'joined_at' => $joinedAtIso,
        ];
    }
}
