<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeLog>
 */
class TimeLogFactory extends Factory
{
    protected $model = TimeLog::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'minutes' => fake()->numberBetween(30, 240),
            'logged_on' => now()->toDateString(),
            'note' => fake()->sentence(),
        ];
    }
}
