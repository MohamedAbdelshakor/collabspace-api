<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => ReportType::TasksSummary,
            'note' => fake()->sentence(),
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->toDateString(),
            'status' => ReportStatus::Ready,
            'data' => ['total_tasks' => 10, 'completed' => 8],
            'ai_summary' => fake()->paragraph(),
        ];
    }
}
