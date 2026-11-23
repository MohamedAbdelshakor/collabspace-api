<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthenticateUserAction
{
    /**
     * @param  array{email: string, password: string, remember_me?: bool|null}  $credentials
     * @return object{user: User, token: string, expiresAt: CarbonInterface}
     *
     * @throws ValidationException
     */
    public function handle(array $credentials): object
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $user->password || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user->forceFill([
            'last_active_at' => now(),
        ])->save();

        $remember = (bool) ($credentials['remember_me'] ?? false);
        $expiresAt = $remember ? now()->addDays(30) : now()->addHours(24);

        $token = $user->createToken(
            name: 'auth_token',
            abilities: ['*'],
            expiresAt: $expiresAt
        )->plainTextToken;

        return (object) [
            'user' => $user,
            'token' => $token,
            'expiresAt' => $expiresAt,
        ];
    }
}
