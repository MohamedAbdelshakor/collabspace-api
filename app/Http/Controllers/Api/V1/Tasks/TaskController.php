<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tasks;

use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use App\Actions\Tasks\UpdateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tasks\CreateTaskRequest;
use App\Http\Requests\Api\V1\Tasks\UpdateTaskRequest;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class TaskController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $tasks = QueryBuilder::for(Task::where('project_id', $project->id))
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::scope('assigned_to')
            )
            ->allowedSorts('position', 'due_date', 'created_at', 'priority')
            ->defaultSort('position')
            ->with(['project', 'creator', 'reviewer', 'assignees', 'timeLogs'])
            ->withCount('comments')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(CreateTaskRequest $request, Project $project, CreateTaskAction $action): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);

        /** @var User $creator */
        $creator = $request->user();

        $task = $action->handle($project, $creator, $request->validated());

        return ApiResponse::success(
            data: new TaskResource($task),
            message: 'Task created successfully.',
            status: 201
        );
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['project', 'creator', 'reviewer', 'assignees', 'timeLogs'])
            ->loadCount('comments');

        return ApiResponse::success(
            data: new TaskResource($task)
        );
    }

    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $action): JsonResponse
    {
        $this->authorize('update', $task);

        $updated = $action->handle($task, $request->validated());

        return ApiResponse::success(
            data: new TaskResource($updated),
            message: 'Task updated successfully.'
        );
    }

    public function destroy(Request $request, Task $task, DeleteTaskAction $action): JsonResponse
    {
        $this->authorize('delete', $task);

        $action->handle($task);

        return ApiResponse::success(
            data: null,
            message: 'Task deleted successfully.'
        );
    }
}
