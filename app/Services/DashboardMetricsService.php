<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

final class DashboardMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function getStats(User $user): array
    {
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', TaskStatus::Completed)->count();
        $taskRate = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 70;

        $totalProjects = Project::count();
        $completedProjects = Project::where('status', ProjectStatus::Completed)->count();
        $projectsRate = $totalProjects > 0 ? (int) round(($completedProjects / $totalProjects) * 100) : 88;

        return [
            'task_completion_rate' => [
                'value' => $taskRate,
                'unit' => '%',
                'change_percentage' => 3,
                'trend' => 'up',
                'change_label' => '+3% From last month',
            ],
            'projects_completion_rate' => [
                'value' => $projectsRate,
                'unit' => '%',
                'change_percentage' => 6,
                'trend' => 'up',
                'change_label' => '+6% From last month',
            ],
            'teams_activity_rate' => [
                'value' => 67,
                'unit' => '%',
                'change_percentage' => -4,
                'trend' => 'down',
                'change_label' => '-4% From last month',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getReportsMetrics(User $user, ?string $period = 'monthly', ?string $projectUlid = null): array
    {
        $stats = $this->getStats($user);

        return [
            'overview' => $stats,
            'task_completion_time_vs_deadline' => [
                'title' => 'Task Completion Time vs Deadline',
                'type' => 'bar',
                'data' => [
                    ['month' => 'Jan', 'value' => 88],
                    ['month' => 'Feb', 'value' => 58],
                    ['month' => 'Mar', 'value' => 40],
                    ['month' => 'Apl', 'value' => 74],
                    ['month' => 'May', 'value' => 74],
                    ['month' => 'Jun', 'value' => 84],
                    ['month' => 'Jul', 'value' => 84],
                    ['month' => 'Aug', 'value' => 84],
                    ['month' => 'Sep', 'value' => 84],
                    ['month' => 'Oct', 'value' => 84],
                ],
            ],
            'projects_progress' => [
                'title' => 'Projects Progress',
                'type' => 'area',
                'selected_filter' => $projectUlid ?? 'all',
                'data' => [
                    ['period' => 'Jan', 'progress' => 30],
                    ['period' => 'Feb', 'progress' => 38],
                    ['period' => 'Mar', 'progress' => 50],
                    ['period' => 'Apl', 'progress' => 52],
                    ['period' => 'May', 'progress' => 65],
                    ['period' => 'Jun', 'progress' => 88],
                    ['period' => '2016', 'progress' => 90],
                    ['period' => '2017', 'progress' => 94],
                    ['period' => '2018', 'progress' => 97],
                    ['period' => '2019', 'progress' => 100],
                ],
            ],
            'worker_activity_rate' => [
                'title' => 'Worker Activity Rate',
                'type' => 'line',
                'selected_period' => $period ?? 'monthly',
                'data' => [
                    ['period' => 'Jan', 'rate' => 10],
                    ['period' => 'Feb', 'rate' => 18],
                    ['period' => 'Mar', 'rate' => 24],
                    ['period' => 'Apl', 'rate' => 30],
                    ['period' => 'May', 'rate' => 48],
                    ['period' => 'Jun', 'rate' => 54],
                    ['period' => '2016', 'rate' => 56],
                    ['period' => '2017', 'rate' => 60],
                    ['period' => '2018', 'rate' => 92],
                    ['period' => '2019', 'rate' => 100],
                ],
            ],
        ];
    }
}
