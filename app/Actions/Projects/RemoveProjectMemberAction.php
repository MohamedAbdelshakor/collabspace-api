<?php

declare(strict_types=1);

namespace App\Actions\Projects;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class RemoveProjectMemberAction
{
    public function handle(Project $project, User $user): void
    {
        DB::transaction(function () use ($project, $user): void {
            $project->members()->detach($user->id);

            // Detach user from project conversations
            /** @var Collection<int, Conversation> $conversations */
            $conversations = $project->conversations()->get();
            foreach ($conversations as $conversation) {
                $conversation->participants()->detach($user->id);
            }
        });
    }
}
