<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportType: string
{
    case TasksSummary = 'tasks_summary';
    case ProjectStatus = 'project_status';
    case TeamPerformance = 'team_performance';
    case TimeTracking = 'time_tracking';
    case MeetingsAttendance = 'meetings_attendance';

    public function label(): string
    {
        return match ($this) {
            self::TasksSummary => 'Tasks Summary Report',
            self::ProjectStatus => 'Project Status Report',
            self::TeamPerformance => 'Team Performance Report',
            self::TimeTracking => 'Time Tracking Report',
            self::MeetingsAttendance => 'Meetings Attendance Report',
        };
    }
}
