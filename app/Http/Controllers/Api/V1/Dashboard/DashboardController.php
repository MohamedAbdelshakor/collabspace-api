<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardMetricsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function stats(Request $request, DashboardMetricsService $metricsService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $stats = $metricsService->getStats($user);

        return ApiResponse::success(
            data: $stats
        );
    }
}
