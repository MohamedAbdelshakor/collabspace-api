<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class RegisterUserAction
{
    /**
     * @param  array{name: string, email: string, password: string, remember_me?: bool|null}  $data
     * @return object{user: User, token: string, expiresAt: CarbonInterface}
     */
    public function handle(array $data): object
    {
        return DB::transaction(function () use ($data): object {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('member');

            event(new Registered($user));

            $remember = (bool) ($data['remember_me'] ?? false);
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
        });
    }
}
