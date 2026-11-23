<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Projects;

use App\Actions\Projects\AddProjectMemberAction;
use App\Actions\Projects\RemoveProjectMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Projects\AddProjectMemberRequest;
use App\Http\Resources\Api\V1\ProjectMemberResource;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProjectMemberController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        return ProjectMemberResource::collection($project->members);
    }

    public function store(
        AddProjectMemberRequest $request,
        Project $project,
        AddProjectMemberAction $action
    ): JsonResponse {
        $this->authorize('manageMembers', $project);

        /** @var User $targetUser */
        $targetUser = User::where('ulid', $request->validated('user_id'))->firstOrFail();

        $department = null;
        if ($departmentUlid = $request->validated('department_id')) {
            /** @var Department|null $department */
            $department = Department::where('ulid', $departmentUlid)->first();
        }

        $action->handle(
            project: $project,
            user: $targetUser,
            department: $department,
            roleTitle: $request->validated('role_title'),
            isLeader: (bool) $request->validated('is_leader', false)
        );

        return ApiResponse::success(
            data: null,
            message: 'Member added to project successfully.',
            status: 201
        );
    }

    public function destroy(
        Request $request,
        Project $project,
        User $user,
        RemoveProjectMemberAction $action
    ): JsonResponse {
        $this->authorize('manageMembers', $project);

        $action->handle($project, $user);

        return ApiResponse::success(
            data: null,
            message: 'Member removed from project successfully.'
        );
    }
}
