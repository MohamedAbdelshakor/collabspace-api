<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_project_tasks_ordered_by_position(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Second Position',
            'position' => 1,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'First Position',
            'position' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/projects/{$project->ulid}/tasks");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertSame('First Position', $data[0]['title']);
        $this->assertSame('Second Position', $data[1]['title']);
    }

    public function test_user_can_create_task_with_assignees(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $reviewer = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/projects/{$project->ulid}/tasks", [
            'title' => 'Design System Tokens',
            'description' => 'Extract colors and typography from Figma',
            'priority' => TaskPriority::High->value,
            'status' => TaskStatus::Todo->value,
            'reviewer_id' => $reviewer->ulid,
            'assignee_ids' => [$assignee->ulid],
            'due_date' => '2026-04-15',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Design System Tokens')
            ->assertJsonPath('data.priority', TaskPriority::High->value)
            ->assertJsonPath('data.reviewer.id', $reviewer->ulid);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Design System Tokens',
            'reviewer_id' => $reviewer->id,
        ]);

        $this->assertDatabaseHas('task_assignees', [
            'user_id' => $assignee->id,
        ]);
    }

    public function test_user_can_view_single_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'title' => 'Detailed Task']);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/tasks/{$task->ulid}");

        $response->assertOk()
            ->assertJsonPath('data.id', $task->ulid)
            ->assertJsonPath('data.title', 'Detailed Task');
    }

    public function test_user_can_delete_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/tasks/{$task->ulid}");

        $response->assertOk();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
