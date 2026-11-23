<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\TaskStatus;
use App\Models\Conversation;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_populate_complete_figma_domain(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Users & Roles
        $salah = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($salah);
        $this->assertEquals('Mohamed Salah', $salah->name);
        $this->assertEquals('Senior UI/UX Designer', $salah->job_title);
        $this->assertEquals(26, strlen($salah->ulid));
        $this->assertTrue($salah->hasRole('admin'));

        $this->assertGreaterThanOrEqual(17, User::count());

        // Departments
        $this->assertEquals(4, Department::count());
        $this->assertDatabaseHas('departments', ['slug' => 'ui-ux']);
        $this->assertDatabaseHas('departments', ['slug' => 'frontend']);
        $this->assertDatabaseHas('departments', ['slug' => 'backend']);
        $this->assertDatabaseHas('departments', ['slug' => 'marketing']);

        // Projects
        $alpha = Project::where('name', 'Alpha')->first();
        $this->assertNotNull($alpha);
        $this->assertEquals(42, $alpha->progress);
        $this->assertEquals(26, strlen($alpha->ulid));
        $this->assertGreaterThanOrEqual(10, $alpha->members()->count());

        $this->assertDatabaseHas('projects', ['name' => 'SepetGo']);

        // Kanban Tasks
        $this->assertDatabaseHas('tasks', ['title' => 'Creat Wireframes', 'status' => TaskStatus::Todo->value]);
        $this->assertDatabaseHas('tasks', ['title' => 'Chat Screen Prototyping', 'status' => TaskStatus::InProgress->value]);
        $this->assertDatabaseHas('tasks', ['title' => 'Mobile Responsiveness', 'status' => TaskStatus::InReview->value]);
        $this->assertDatabaseHas('tasks', ['title' => 'Login Screen', 'status' => TaskStatus::Completed->value]);

        $inReviewTask = Task::where('title', 'Mobile Responsiveness')->first();
        $this->assertNotNull($inReviewTask);
        $this->assertNotNull($inReviewTask->reviewer_id);
        $this->assertEquals('Ali Hassan', $inReviewTask->reviewer?->name);

        // Meetings & Transcripts
        $meeting = Meeting::where('title', 'Design Review')->first();
        $this->assertNotNull($meeting);
        $this->assertEquals(5, $meeting->attendees()->count());
        $this->assertEquals(6, $meeting->transcriptSegments()->count());
        $this->assertNotNull($meeting->summaries()->first());

        // Conversations & Channels
        $this->assertGreaterThanOrEqual(3, Conversation::count());
    }
}
