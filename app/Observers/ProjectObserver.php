<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ConversationType;
use App\Models\Conversation;
use App\Models\Project;

final class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        // Business Rule: Creating a project automatically initializes a public channel conversation
        $conversation = Conversation::create([
            'project_id' => $project->id,
            'name' => $project->name,
            'type' => ConversationType::Channel,
        ]);

        if ($project->owner_id) {
            $conversation->participants()->attach($project->owner_id, [
                'joined_at' => now(),
            ]);
        }
    }
}
