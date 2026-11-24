<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Reports;

use App\Enums\ReportType;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_dashboard_and_reports_metrics(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Dashboard Stats
        $dashboardResponse = $this->getJson('/api/v1/dashboard/stats');
        $dashboardResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'task_completion_rate' => ['value', 'unit', 'trend'],
                    'projects_completion_rate' => ['value', 'unit', 'trend'],
                    'teams_activity_rate' => ['value', 'unit', 'trend'],
                ],
            ]);

        // Reports Metrics
        $reportsMetricsResponse = $this->getJson('/api/v1/reports/metrics');
        $reportsMetricsResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'overview',
                    'task_completion_time_vs_deadline' => [
                        'title',
                        'type',
                        'data',
                    ],
                    'projects_progress' => [
                        'title',
                        'type',
                        'data',
                    ],
                    'worker_activity_rate' => [
                        'title',
                        'type',
                        'data',
                    ],
                ],
            ]);
    }

    public function test_user_can_generate_detailed_report_with_csv_and_pdf(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $project = Project::factory()->create();
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        Sanctum::actingAs($user);

        $payload = [
            'type' => ReportType::TasksSummary->value,
            'note' => 'Quarterly review of current task workloads.',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/v1/reports', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', ReportType::TasksSummary->value)
            ->assertJsonPath('data.status', 'ready');

        $reportUlid = $response->json('data.id');
        $this->assertNotNull($response->json('data.csv_url'));
        $this->assertNotNull($response->json('data.pdf_url'));
        $this->assertNotEmpty($response->json('data.ai_summary'));
        $this->assertNotEmpty($response->json('data.data'));

        // Verify files in fake storage
        Storage::disk('public')->assertExists("reports/csv/{$reportUlid}.csv");
        Storage::disk('public')->assertExists("reports/pdf/{$reportUlid}.pdf");
    }

    public function test_user_can_download_csv_and_pdf_exports(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reports', [
            'type' => ReportType::ProjectStatus->value,
            'note' => 'Project status overview',
        ]);

        $reportUlid = $response->json('data.id');

        // CSV Export
        $csvResponse = $this->get("/api/v1/reports/{$reportUlid}/export/csv");
        $csvResponse->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // PDF Export
        $pdfResponse = $this->get("/api/v1/reports/{$reportUlid}/export/pdf");
        $pdfResponse->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_stranger_cannot_access_or_export_another_users_report(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        Sanctum::actingAs($owner);
        $createResponse = $this->postJson('/api/v1/reports', [
            'type' => ReportType::TeamPerformance->value,
        ]);
        $reportUlid = $createResponse->json('data.id');

        // Stranger tries to view
        Sanctum::actingAs($stranger);
        $this->getJson("/api/v1/reports/{$reportUlid}")->assertStatus(403);
        $this->get("/api/v1/reports/{$reportUlid}/export/csv")->assertStatus(403);
        $this->get("/api/v1/reports/{$reportUlid}/export/pdf")->assertStatus(403);
    }
}
