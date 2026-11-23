<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tasks;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class TimeLogAndCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_time_on_task_and_list_logs(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/tasks/{$task->ulid}/time-logs", [
            'minutes' => 120,
            'logged_on' => '2026-03-20',
            'note' => 'Worked on responsive design components',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.minutes', 120)
            ->assertJsonPath('data.note', 'Worked on responsive design components');

        $this->assertDatabaseHas('time_logs', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'minutes' => 120,
        ]);

        $listResponse = $this->getJson("/api/v1/tasks/{$task->ulid}/time-logs");
        $listResponse->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_delete_own_time_log(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        $log = TimeLog::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/time-logs/{$log->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('time_logs', ['id' => $log->id]);
    }

    public function test_user_can_add_comment_to_task_and_list_comments(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/tasks/{$task->ulid}/comments", [
            'body' => 'I reviewed the initial layout and left notes on Figma.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'I reviewed the initial layout and left notes on Figma.');

        $this->assertDatabaseHas('comments', [
            'commentable_type' => $task->getMorphClass(),
            'commentable_id' => $task->id,
            'user_id' => $user->id,
        ]);

        $listResponse = $this->getJson("/api/v1/tasks/{$task->ulid}/comments");
        $listResponse->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        $comment = Comment::factory()->create([
            'commentable_type' => $task->getMorphClass(),
            'commentable_id' => $task->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/comments/{$comment->ulid}");

        $response->assertOk();
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}
