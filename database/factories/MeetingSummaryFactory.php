<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingSummary>
 */
final class MeetingSummaryFactory extends Factory
{
    protected $model = MeetingSummary::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'language' => 'en',
            'key_points' => [
                fake()->sentence(),
                fake()->sentence(),
            ],
            'action_items' => [
                ['task' => fake()->sentence(), 'assignee' => fake()->name()],
            ],
            'generated_at' => now(),
        ];
    }
}
