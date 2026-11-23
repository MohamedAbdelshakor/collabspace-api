<?php

declare(strict_types=1);

namespace App\Actions\Projects;

use App\Models\Project;

final class UpdateProjectAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }
}
