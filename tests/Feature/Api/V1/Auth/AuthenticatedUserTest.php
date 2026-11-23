<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AuthenticatedUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_me_endpoint_returns_authenticated_user_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Profile User',
            'email' => 'profile@example.com',
            'job_title' => 'Product Manager',
        ]);
        $user->assignRole('member');

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.name', 'Profile User')
            ->assertJsonPath('data.email', 'profile@example.com')
            ->assertJsonPath('data.job_title', 'Product Manager')
            ->assertJsonPath('data.roles.0', 'member');
    }

    public function test_unauthenticated_request_to_me_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('device_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJsonPath('message', __('collabspace.logout_success'));

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_logout_all_revokes_all_user_tokens(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('device1');
        $user->createToken('device2');
        $user->createToken('device3');

        $this->assertEquals(3, $user->tokens()->count());

        $response = $this->withHeader('Authorization', 'Bearer '.$token1->plainTextToken)
            ->postJson('/api/v1/auth/logout-all');

        $response->assertOk()
            ->assertJsonPath('message', __('collabspace.logout_all_success'));

        $this->assertEquals(0, $user->tokens()->count());
    }
}
