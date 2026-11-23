<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $token
 * @property-read CarbonInterface $expiresAt
 * @property-read User $user
 */
final class AuthResponseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'token_type' => 'Bearer',
            'expires_at' => $this->expiresAt->toIso8601String(),
            'user' => new UserResource($this->user),
        ];
    }
}
