<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tasks;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class TaskStatusAndReviewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_moving_task_to_in_review_without_reviewer_is_forbidden(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::InProgress,
            'reviewer_id' => null, // No reviewer!
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/tasks/{$task->ulid}/status", [
            'status' => TaskStatus::InReview->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'TASK_REVIEWER_REQUIRED');
    }

    public function test_moving_task_to_in_review_with_reviewer_succeeds(): void
    {
        $user = User::factory()->create();
        $reviewer = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::InProgress,
            'reviewer_id' => $reviewer->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/tasks/{$task->ulid}/status", [
            'status' => TaskStatus::InReview->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', TaskStatus::InReview->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::InReview->value,
        ]);
    }

    public function test_status_change_records_history_and_recalculates_project_progress(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id, 'progress' => 0]);

        $task1 = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::InProgress,
        ]);

        $task2 = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
        ]);

        Sanctum::actingAs($user);

        // Move task1 to Completed (1 out of 2 tasks done = 50% project progress)
        $response = $this->patchJson("/api/v1/tasks/{$task1->ulid}/status", [
            'status' => TaskStatus::Completed->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', TaskStatus::Completed->value);

        // Verifies task_status_histories record created
        $this->assertDatabaseHas('task_status_histories', [
            'task_id' => $task1->id,
            'from_status' => TaskStatus::InProgress->value,
            'to_status' => TaskStatus::Completed->value,
            'user_id' => $user->id,
        ]);

        // Verifies project progress automatically updated to 50%
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'progress' => 50,
        ]);
    }
}
