<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_forgot_password_returns_generic_message_for_existing_user(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'existing@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', __('collabspace.forgot_password_sent'));
    }

    public function test_forgot_password_returns_same_generic_message_for_non_existent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Prevents User Enumeration by returning the exact same response
        $response->assertOk()
            ->assertJsonPath('message', __('collabspace.forgot_password_sent'));
    }

    public function test_reset_password_with_valid_token_updates_password(): void
    {
        $user = User::factory()->create([
            'email' => 'resetme@example.com',
            'password' => Hash::make('OldPassword1!'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'resetme@example.com',
            'password' => 'NewStr0ngP@ss!',
            'password_confirmation' => 'NewStr0ngP@ss!',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', __('collabspace.password_reset_success'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewStr0ngP@ss!', $user->password));
    }

    public function test_reset_password_with_invalid_token_fails(): void
    {
        User::factory()->create(['email' => 'resetme@example.com']);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token-here',
            'email' => 'resetme@example.com',
            'password' => 'NewStr0ngP@ss!',
            'password_confirmation' => 'NewStr0ngP@ss!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonValidationErrors(['email']);
    }
}
