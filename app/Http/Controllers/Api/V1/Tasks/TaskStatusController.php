<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tasks;

use App\Actions\Tasks\ChangeTaskStatusAction;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tasks\ChangeTaskStatusRequest;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Task;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class TaskStatusController extends Controller
{
    public function update(ChangeTaskStatusRequest $request, Task $task, ChangeTaskStatusAction $action): JsonResponse
    {
        $this->authorize('update', $task);

        $targetStatus = TaskStatus::from((string) $request->validated('status'));
        $updated = $action->handle($task, $targetStatus);

        return ApiResponse::success(
            data: new TaskResource($updated),
            message: 'Task status updated successfully.'
        );
    }
}
