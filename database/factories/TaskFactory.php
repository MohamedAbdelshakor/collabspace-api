<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'created_by' => User::factory(),
            'reviewer_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => TaskStatus::Todo,
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'progress' => 0,
            'position' => fake()->numberBetween(0, 100),
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ];
    }
}
