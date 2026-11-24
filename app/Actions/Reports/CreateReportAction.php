<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportGeneratorService;

final class CreateReportAction
{
    public function __construct(
        private readonly ReportGeneratorService $generator
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): Report
    {
        $type = ReportType::from((string) $data['type']);

        $startDate = $data['start_date'] ?? now()->subMonth()->toDateString();
        $endDate = $data['end_date'] ?? now()->toDateString();

        /** @var Report $report */
        $report = Report::create([
            'user_id' => $user->id,
            'type' => $type,
            'note' => $data['note'] ?? null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => ReportStatus::Pending,
        ]);

        return $this->generator->generate($report);
    }
}
