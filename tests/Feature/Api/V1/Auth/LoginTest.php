<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'loginuser@example.com',
            'password' => 'Secret123!',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_at',
                    'user',
                ],
                'message',
            ])
            ->assertJsonPath('data.user.email', 'loginuser@example.com');

        $user->refresh();
        $this->assertNotNull($user->last_active_at);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'loginuser@example.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rate_limiting_protects_against_brute_force(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'brute@example.com',
                'password' => 'WrongPassword!',
            ]);
        }

        // 6th attempt should be blocked by throttle:login rate limiter
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'brute@example.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('code', 'TOO_MANY_REQUESTS');
    }

    public function test_login_remember_me_sets_longer_token_expiry(): void
    {
        User::factory()->create([
            'email' => 'rememberuser@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'rememberuser@example.com',
            'password' => 'Secret123!',
            'remember_me' => true,
        ]);

        $response->assertOk();

        $expiresAt = $response->json('data.expires_at');
        $daysUntilExpiry = now()->diffInDays(now()->parse($expiresAt));
        $this->assertGreaterThanOrEqual(29, $daysUntilExpiry);
    }
}
