<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MeetingRecordingStatus;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+2 weeks');
        $endsAt = (clone $startsAt)->modify('+1 hour');

        return [
            'project_id' => null,
            'organizer_id' => User::factory(),
            'conversation_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'meeting_url' => fake()->url(),
            'status' => MeetingStatus::Scheduled,
            'recording_status' => MeetingRecordingStatus::None,
        ];
    }
}
