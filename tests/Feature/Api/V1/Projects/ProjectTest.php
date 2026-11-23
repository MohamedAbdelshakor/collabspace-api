<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Projects;

use App\Enums\ConversationType;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_visible_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Project 1: owned by user
        Project::factory()->create([
            'owner_id' => $user->id,
            'name' => 'My Owned Project',
        ]);

        // Project 2: user is member
        $memberProject = Project::factory()->create([
            'owner_id' => $otherUser->id,
            'name' => 'Team Project',
        ]);
        $memberProject->members()->attach($user->id, ['joined_at' => now()]);

        // Project 3: completely unrelated project
        Project::factory()->create([
            'owner_id' => $otherUser->id,
            'name' => 'Secret Project',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'My Owned Project'])
            ->assertJsonFragment(['name' => 'Team Project'])
            ->assertJsonMissing(['name' => 'Secret Project']);
    }

    public function test_user_can_create_project_and_it_initializes_channel_and_leader(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'name' => 'SepetGo E-Commerce',
            'description' => 'Pet website redesign',
            'type' => ProjectType::WebApp->value,
            'priority' => ProjectPriority::High->value,
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
        ];

        $response = $this->postJson('/api/v1/projects', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'SepetGo E-Commerce')
            ->assertJsonPath('data.progress', 0)
            ->assertJsonPath('data.status', ProjectStatus::Active->value);

        $this->assertDatabaseHas('projects', [
            'name' => 'SepetGo E-Commerce',
            'owner_id' => $user->id,
            'progress' => 0,
        ]);

        /** @var Project $project */
        $project = Project::where('name', 'SepetGo E-Commerce')->firstOrFail();

        // Verifies creator is registered as project leader
        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'is_leader' => true,
        ]);

        // Verifies public channel was initialized
        $this->assertDatabaseHas('conversations', [
            'project_id' => $project->id,
            'name' => 'SepetGo E-Commerce',
            'type' => ConversationType::Channel->value,
        ]);
    }

    public function test_user_can_view_project_details(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/projects/{$project->ulid}");

        $response->assertOk()
            ->assertJsonPath('data.id', $project->ulid)
            ->assertJsonPath('data.name', $project->name);
    }

    public function test_unauthorized_user_cannot_view_private_project(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        Sanctum::actingAs($stranger);

        $response = $this->getJson("/api/v1/projects/{$project->ulid}");

        $response->assertForbidden();
    }

    public function test_owner_can_update_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id, 'name' => 'Original Name']);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/projects/{$project->ulid}", [
            'name' => 'Updated Project Name',
            'status' => ProjectStatus::Active->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Project Name')
            ->assertJsonPath('data.status', ProjectStatus::Active->value);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
        ]);
    }

    public function test_owner_can_delete_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/projects/{$project->ulid}");

        $response->assertOk();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}
