<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConversationType;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type' => ConversationType::Direct,
            'project_id' => null,
            'department_id' => null,
            'name' => null,
            'direct_key' => fake()->uuid(),
        ];
    }
}
