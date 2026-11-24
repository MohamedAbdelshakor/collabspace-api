<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\TranscriptSegment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TranscriptSegment>
 */
final class TranscriptSegmentFactory extends Factory
{
    protected $model = TranscriptSegment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'speaker_id' => User::factory(),
            'start_second' => fake()->numberBetween(0, 3600),
            'language' => 'en',
            'text' => fake()->sentence(),
        ];
    }
}
