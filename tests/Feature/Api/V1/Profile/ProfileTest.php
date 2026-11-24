<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Profile;

use App\Enums\AvailabilityStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_with_metrics_and_activities(): void
    {
        $user = User::factory()->create([
            'name' => 'Mohamed Salah',
            'job_title' => 'Senior UI/UX Designer',
            'years_of_experience' => 6,
            'phone_country' => 'EG',
            'phone_number' => '+201234569878',
            'bio' => 'Experienced UI/UX Designer with over 5 years of expertise.',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonPath('data.name', 'Mohamed Salah')
            ->assertJsonPath('data.job_title', 'Senior UI/UX Designer')
            ->assertJsonPath('data.phone_number', '+201234569878')
            ->assertJsonPath('data.metrics.completed_tasks', 140)
            ->assertJsonPath('data.metrics.hours_worked', 345)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'job_title',
                    'years_of_experience',
                    'phone_country',
                    'phone_number',
                    'availability_status',
                    'bio',
                    'metrics' => ['completed_tasks', 'hours_worked'],
                    'joined_label',
                    'recent_activities',
                ],
            ]);
    }

    public function test_user_can_update_profile_with_phone_validation(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Sanctum::actingAs($user);

        // 1. Invalid phone number fails with 422
        $invalidResponse = $this->patchJson('/api/v1/profile', [
            'phone_number' => '12345-not-a-valid-phone',
        ]);
        $invalidResponse->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        // 2. Valid Egyptian phone number succeeds
        $validResponse = $this->patchJson('/api/v1/profile', [
            'name' => 'Mohamed Salah Updated',
            'phone_country' => 'EG',
            'phone_number' => '+201234569878',
            'availability_status' => AvailabilityStatus::Busy->value,
            'job_title' => 'Lead Product Designer',
            'years_of_experience' => 7,
            'current_project_id' => $project->ulid,
            'bio' => 'Updated biography with design leadership experience.',
        ]);

        $validResponse->assertOk()
            ->assertJsonPath('data.name', 'Mohamed Salah Updated')
            ->assertJsonPath('data.phone_number', '+201234569878')
            ->assertJsonPath('data.availability_status', AvailabilityStatus::Busy->value)
            ->assertJsonPath('data.job_title', 'Lead Product Designer')
            ->assertJsonPath('data.years_of_experience', 7);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Mohamed Salah Updated',
            'phone_number' => '+201234569878',
            'job_title' => 'Lead Product Designer',
        ]);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $fakeAvatar = UploadedFile::fake()->create('avatar.jpg', 200, 'image/jpeg');

        $response = $this->postJson('/api/v1/profile/avatar', [
            'avatar' => $fakeAvatar,
        ]);

        $response->assertOk();
        $this->assertNotNull($response->json('data.avatar_url'));

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
    }

    public function test_user_can_view_profile_tasks_and_files(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Tasks tab
        $tasksResponse = $this->getJson('/api/v1/profile/tasks');
        $tasksResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'to_do' => ['count', 'items'],
                    'done' => ['count', 'items'],
                ],
            ]);

        // Files tab
        $filesResponse = $this->getJson('/api/v1/profile/files');
        $filesResponse->assertOk()
            ->assertJsonCount(9, 'data')
            ->assertJsonPath('data.0.title', 'Design mockups')
            ->assertJsonPath('data.0.author', 'Ahmed Hassan')
            ->assertJsonPath('data.0.file_type', 'pdf');
    }
}
