<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Reports;

use App\Actions\Reports\CreateReportAction;
use App\Enums\ReportType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Reports\CreateReportRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Models\Report;
use App\Models\User;
use App\Services\DashboardMetricsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportController extends Controller
{
    public function metrics(Request $request, DashboardMetricsService $metricsService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $period = $request->query('period') ? (string) $request->query('period') : 'monthly';
        $projectUlid = $request->query('project_id') ? (string) $request->query('project_id') : null;

        $metrics = $metricsService->getReportsMetrics($user, $period, $projectUlid);

        return ApiResponse::success(
            data: $metrics
        );
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $query = Report::query();
        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $reports = $query->with('user')->latest('id')->paginate(15);

        return ReportResource::collection($reports);
    }

    public function store(CreateReportRequest $request, CreateReportAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $report = $action->handle($user, $request->validated());

        return ApiResponse::success(
            data: new ReportResource($report),
            message: 'Report generated successfully.',
            status: 201
        );
    }

    public function show(Request $request, Report $report): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasRole('admin') && $report->user_id !== $user->id) {
            return ApiResponse::error(
                message: 'This action is unauthorized.',
                code: 'FORBIDDEN',
                status: 403
            );
        }

        $report->load('user');

        return ApiResponse::success(
            data: new ReportResource($report)
        );
    }

    public function exportCsv(Request $request, Report $report): StreamedResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasRole('admin') && $report->user_id !== $user->id) {
            return ApiResponse::error(
                message: 'This action is unauthorized.',
                code: 'FORBIDDEN',
                status: 403
            );
        }

        if (! $report->csv_path || ! Storage::disk('public')->exists($report->csv_path)) {
            return ApiResponse::error(
                message: 'CSV file not found.',
                code: 'NOT_FOUND',
                status: 404
            );
        }

        $typeValue = $report->type instanceof ReportType ? $report->type->value : (string) $report->type;
        $filename = "{$typeValue}-{$report->ulid}.csv";

        return Storage::disk('public')->download($report->csv_path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request, Report $report): StreamedResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasRole('admin') && $report->user_id !== $user->id) {
            return ApiResponse::error(
                message: 'This action is unauthorized.',
                code: 'FORBIDDEN',
                status: 403
            );
        }

        if (! $report->pdf_path || ! Storage::disk('public')->exists($report->pdf_path)) {
            return ApiResponse::error(
                message: 'PDF file not found.',
                code: 'NOT_FOUND',
                status: 404
            );
        }

        $typeValue = $report->type instanceof ReportType ? $report->type->value : (string) $report->type;
        $filename = "{$typeValue}-{$report->ulid}.pdf";

        return Storage::disk('public')->download($report->pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
