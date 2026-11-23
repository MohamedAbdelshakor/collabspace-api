<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        Event::fake([Registered::class]);

        $payload = [
            'name' => 'Mohamed Test',
            'email' => 'newuser@example.com',
            'password' => 'Collab#Space!2026!Secured',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'roles',
                    ],
                ],
                'message',
            ])
            ->assertJsonPath('data.user.email', 'newuser@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'Mohamed Test',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('member'));

        Event::assertDispatched(Registered::class);
    }

    public function test_registration_fails_with_weak_password(): void
    {
        $payload = [
            'name' => 'Mohamed Test',
            'email' => 'weak@example.com',
            'password' => '123456',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $payload = [
            'name' => 'Mohamed Duplicate',
            'email' => 'duplicate@example.com',
            'password' => 'Str0ngP@ssw0rd!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_remember_me_sets_30_day_token_expiry(): void
    {
        $payload = [
            'name' => 'Remember User',
            'email' => 'remember@example.com',
            'password' => 'Collab#Space!2026!Secured',
            'remember_me' => true,
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertCreated();

        $expiresAt = $response->json('data.expires_at');
        $this->assertNotNull($expiresAt);

        $daysUntilExpiry = now()->diffInDays(now()->parse($expiresAt));
        $this->assertGreaterThanOrEqual(29, $daysUntilExpiry);
    }
}
