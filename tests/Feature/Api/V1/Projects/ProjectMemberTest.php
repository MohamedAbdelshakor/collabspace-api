<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Projects;

use App\Models\Conversation;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ProjectMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_member_and_syncs_to_channel(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['job_title' => 'Frontend Engineer']);
        $department = Department::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $conversation = Conversation::factory()->create(['project_id' => $project->id]);

        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/v1/projects/{$project->ulid}/members", [
            'user_id' => $member->ulid,
            'department_id' => $department->ulid,
            'role_title' => 'Lead Frontend',
            'is_leader' => false,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role_title' => 'Lead Frontend',
        ]);

        // Verifies synced to conversation
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_can_remove_member_from_project_and_channel(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($member->id, ['joined_at' => now()]);

        $conversation = Conversation::factory()->create(['project_id' => $project->id]);
        $conversation->participants()->attach($member->id, ['joined_at' => now()]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/v1/projects/{$project->ulid}/members/{$member->ulid}");

        $response->assertOk();

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);

        $this->assertDatabaseMissing('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $member->id,
        ]);
    }
}
