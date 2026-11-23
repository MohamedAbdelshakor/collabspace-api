<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

final class HandleGoogleAuthAction
{
    /**
     * @param  array{id_token: string, remember_me?: bool|null}  $payload
     * @return object{user: User, token: string, expiresAt: CarbonInterface}
     *
     * @throws ValidationException
     */
    public function handle(array $payload): object
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = $driver->userFromToken($payload['id_token']);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'id_token' => [__('collabspace.invalid_google_token')],
            ]);
        }

        return DB::transaction(function () use ($googleUser, $payload): object {
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: 'User',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar_path' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => null,
                ]);

                $user->assignRole('member');
            } else {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_active_at' => now(),
                ])->save();
            }

            $remember = (bool) ($payload['remember_me'] ?? false);
            $expiresAt = $remember ? now()->addDays(30) : now()->addHours(24);

            $token = $user->createToken(
                name: 'google_token',
                abilities: ['*'],
                expiresAt: $expiresAt
            )->plainTextToken;

            return (object) [
                'user' => $user,
                'token' => $token,
                'expiresAt' => $expiresAt,
            ];
        });
    }
}
