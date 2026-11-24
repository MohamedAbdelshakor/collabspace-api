<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\AvailabilityStatus;
use App\Models\Project;
use App\Models\User;

final class UpdateProfileAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        $updateFields = [];

        if (array_key_exists('name', $data)) {
            $updateFields['name'] = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $updateFields['email'] = $data['email'];
        }
        if (array_key_exists('job_title', $data)) {
            $updateFields['job_title'] = $data['job_title'];
        }
        if (array_key_exists('years_of_experience', $data)) {
            $updateFields['years_of_experience'] = $data['years_of_experience'];
        }
        if (array_key_exists('phone_number', $data)) {
            $updateFields['phone_number'] = $data['phone_number'];
        }
        if (array_key_exists('phone_country', $data)) {
            $updateFields['phone_country'] = $data['phone_country'];
        }
        if (array_key_exists('bio', $data)) {
            $updateFields['bio'] = $data['bio'];
        }
        if (array_key_exists('availability_status', $data) && $data['availability_status'] !== null) {
            $updateFields['availability_status'] = AvailabilityStatus::from((string) $data['availability_status']);
        }
        if (array_key_exists('current_project_id', $data) && $data['current_project_id'] !== null) {
            $project = Project::where('ulid', $data['current_project_id'])->first();
            if ($project && ! $user->projects()->where('projects.id', $project->id)->exists()) {
                $user->projects()->attach($project->id, [
                    'role_title' => $data['job_title'] ?? 'Member',
                    'joined_at' => now(),
                ]);
            }
        }

        $user->update($updateFields);

        return $user->refresh()->load('projects');
    }
}
