<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tasks;

use App\Actions\TimeLogs\CreateTimeLogAction;
use App\Actions\TimeLogs\DeleteTimeLogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TimeLogs\CreateTimeLogRequest;
use App\Http\Resources\Api\V1\TimeLogResource;
use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TimeLogController extends Controller
{
    public function index(Request $request, Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        $logs = $task->timeLogs()
            ->with('user')
            ->orderByDesc('logged_on')
            ->paginate(20);

        return TimeLogResource::collection($logs);
    }

    public function store(CreateTimeLogRequest $request, Task $task, CreateTimeLogAction $action): JsonResponse
    {
        $this->authorize('view', $task);

        /** @var User $user */
        $user = $request->user();

        $log = $action->handle(
            task: $task,
            user: $user,
            minutes: (int) $request->validated('minutes'),
            loggedOn: (string) $request->validated('logged_on'),
            note: $request->validated('note')
        );

        return ApiResponse::success(
            data: new TimeLogResource($log),
            message: 'Time logged successfully.',
            status: 201
        );
    }

    public function destroy(Request $request, TimeLog $timeLog, DeleteTimeLogAction $action): JsonResponse
    {
        $this->authorize('delete', $timeLog);

        $action->handle($timeLog);

        return ApiResponse::success(
            data: null,
            message: 'Time log deleted successfully.'
        );
    }
}
