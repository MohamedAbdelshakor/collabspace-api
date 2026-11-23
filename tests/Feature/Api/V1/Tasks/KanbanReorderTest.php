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

final class KanbanReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reorder_kanban_tasks_in_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $task1 = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
            'position' => 0,
        ]);

        $task2 = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
            'position' => 1,
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'tasks' => [
                [
                    'id' => $task1->ulid,
                    'status' => TaskStatus::InProgress->value,
                    'position' => 0,
                ],
                [
                    'id' => $task2->ulid,
                    'status' => TaskStatus::InProgress->value,
                    'position' => 1,
                ],
            ],
        ];

        $response = $this->putJson("/api/v1/projects/{$project->ulid}/tasks/reorder", $payload);

        $response->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $task1->id,
            'status' => TaskStatus::InProgress->value,
            'position' => 0,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task2->id,
            'status' => TaskStatus::InProgress->value,
            'position' => 1,
        ]);
    }
}
