<?php

declare(strict_types=1);

namespace App\Actions\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateProjectAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $creator, array $data): Project
    {
        return DB::transaction(function () use ($creator, $data): Project {
            /** @var Project $project */
            $project = Project::create([
                'owner_id' => $creator->id,
                'name' => (string) $data['name'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'priority' => $data['priority'],
                'status' => $data['status'] ?? ProjectStatus::Active,
                'start_date' => $data['start_date'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'progress' => 0,
            ]);

            // Automatically register creator as project leader
            $project->members()->attach($creator->id, [
                'role_title' => 'Project Lead',
                'is_leader' => true,
                'joined_at' => now(),
            ]);

            return $project->load(['owner', 'members']);
        });
    }
}
