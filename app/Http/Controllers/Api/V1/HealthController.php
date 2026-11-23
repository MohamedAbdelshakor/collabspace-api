<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $databaseConnected = false;

        try {
            DB::connection()->getPdo();
            $databaseConnected = true;
        } catch (\Throwable) {
            $databaseConnected = false;
        }

        return ApiResponse::success(
            data: [
                'status' => $databaseConnected ? 'healthy' : 'degraded',
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0.0',
                'services' => [
                    'database' => $databaseConnected ? 'connected' : 'disconnected',
                    'cache' => 'ready',
                ],
            ],
            message: 'Collabspace API is operational.'
        );
    }
}
