<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AvailabilityStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'job_title' => fake()->jobTitle(),
            'years_of_experience' => fake()->numberBetween(1, 15),
            'availability_status' => AvailabilityStatus::Available,
            'phone_country' => 'EG',
            'phone_number' => '+201'.fake()->numerify('#########'),
            'bio' => fake()->paragraph(),
            'timezone' => 'UTC',
            'locale' => 'en',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function onLeave(): static
    {
        return $this->state(fn (array $attributes): array => [
            'availability_status' => AvailabilityStatus::OnLeave,
        ]);
    }
}
