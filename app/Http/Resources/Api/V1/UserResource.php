<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ulid,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at !== null,
            'job_title' => $this->job_title,
            'years_of_experience' => $this->years_of_experience,
            'avatar_url' => $this->avatar_path,
            'bio' => $this->bio,
            'phone_country' => $this->phone_country,
            'phone_number' => $this->phone_number,
            'availability_status' => $this->availability_status?->value,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'roles' => $this->getRoleNames()->values()->all(),
            'joined_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
