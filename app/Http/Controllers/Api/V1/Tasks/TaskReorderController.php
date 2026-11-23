<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tasks;

use App\Actions\Tasks\ReorderTasksAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tasks\ReorderTasksRequest;
use App\Models\Project;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class TaskReorderController extends Controller
{
    public function update(ReorderTasksRequest $request, Project $project, ReorderTasksAction $action): JsonResponse
    {
        $this->authorize('update', $project);

        /** @var list<array{id: string, status: string, position: int}> $tasks */
        $tasks = $request->validated('tasks');

        $action->handle($project, $tasks);

        return ApiResponse::success(
            data: null,
            message: 'Tasks reordered successfully.'
        );
    }
}
