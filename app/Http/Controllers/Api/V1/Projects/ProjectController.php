<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Projects;

use App\Actions\Projects\CreateProjectAction;
use App\Actions\Projects\DeleteProjectAction;
use App\Actions\Projects\UpdateProjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Projects\CreateProjectRequest;
use App\Http\Requests\Api\V1\Projects\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $projects = QueryBuilder::for(Project::visibleTo($user))
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('priority'),
                AllowedFilter::partial('name')
            )
            ->allowedSorts('name', 'deadline', 'progress', 'created_at')
            ->defaultSort('-created_at')
            ->with(['owner'])
            ->withCount(['members', 'tasks'])
            ->paginate((int) $request->input('per_page', 15));

        return ProjectResource::collection($projects);
    }

    public function store(CreateProjectRequest $request, CreateProjectAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $project = $action->handle($user, $request->validated());

        return ApiResponse::success(
            data: new ProjectResource($project),
            message: 'Project created successfully.',
            status: 201
        );
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['owner', 'members'])
            ->loadCount(['tasks', 'members']);

        return ApiResponse::success(
            data: new ProjectResource($project)
        );
    }

    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action): JsonResponse
    {
        $this->authorize('update', $project);

        $updated = $action->handle($project, $request->validated());
        $updated->load(['owner', 'members'])->loadCount(['tasks', 'members']);

        return ApiResponse::success(
            data: new ProjectResource($updated),
            message: 'Project updated successfully.'
        );
    }

    public function destroy(Request $request, Project $project, DeleteProjectAction $action): JsonResponse
    {
        $this->authorize('delete', $project);

        $action->handle($project);

        return ApiResponse::success(
            data: null,
            message: 'Project deleted successfully.'
        );
    }
}
