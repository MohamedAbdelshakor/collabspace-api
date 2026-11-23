<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', 'now');
        $deadline = fake()->dateTimeBetween('now', '+3 months');

        return [
            'owner_id' => User::factory(),
            'name' => fake()->catchPhrase(),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(ProjectType::cases()),
            'priority' => fake()->randomElement(ProjectPriority::cases()),
            'status' => ProjectStatus::Active,
            'start_date' => $start->format('Y-m-d'),
            'deadline' => $deadline->format('Y-m-d'),
            'progress' => fake()->numberBetween(0, 100),
        ];
    }
}
