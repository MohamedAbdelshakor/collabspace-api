<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Profile;

use App\Actions\Profile\UpdateProfileAction;
use App\Actions\Profile\UploadAvatarAction;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Requests\Api\V1\Profile\UploadAvatarRequest;
use App\Http\Resources\Api\V1\ProfileResource;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->load('projects');

        return ApiResponse::success(
            data: new ProfileResource($user)
        );
    }

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updated = $action->handle($user, $request->validated());

        return ApiResponse::success(
            data: new ProfileResource($updated),
            message: 'Profile updated successfully.'
        );
    }

    public function uploadAvatar(UploadAvatarRequest $request, UploadAvatarAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var UploadedFile $file */
        $file = $request->file('avatar');

        $updated = $action->handle($user, $file);

        return ApiResponse::success(
            data: new ProfileResource($updated),
            message: 'Avatar uploaded successfully.'
        );
    }

    public function tasks(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $tasks = $user->tasks()->with(['project', 'assignees'])->orderBy('position')->get();

        $todoTasks = $tasks->filter(fn (Task $t) => $t->status !== TaskStatus::Completed)->values();
        $doneTasks = $tasks->filter(fn (Task $t) => $t->status === TaskStatus::Completed)->values();

        // If user has no tasks assigned, return Figma default structure
        $todoData = $todoTasks->isNotEmpty()
            ? TaskResource::collection($todoTasks)
            : [
                ['id' => '01JM7T00000000000000000001', 'title' => 'Create user interview', 'status' => 'todo', 'progress' => 45],
                ['id' => '01JM7T00000000000000000002', 'title' => 'Creat Wireframes', 'status' => 'in_progress', 'progress' => 60],
                ['id' => '01JM7T00000000000000000003', 'title' => 'Create user flow', 'status' => 'todo', 'progress' => 20],
                ['id' => '01JM7T00000000000000000004', 'title' => 'Design log in screen', 'status' => 'in_progress', 'progress' => 80],
            ];

        $doneData = $doneTasks->isNotEmpty()
            ? TaskResource::collection($doneTasks)
            : [
                ['id' => '01JM7T00000000000000000005', 'title' => 'Update design system', 'status' => 'completed', 'progress' => 100],
                ['id' => '01JM7T00000000000000000006', 'title' => 'Create information architected', 'status' => 'completed', 'progress' => 100],
                ['id' => '01JM7T00000000000000000007', 'title' => 'Make survey', 'status' => 'completed', 'progress' => 100],
                ['id' => '01JM7T00000000000000000008', 'title' => 'Creat Wireframes', 'status' => 'completed', 'progress' => 100],
                ['id' => '01JM7T00000000000000000009', 'title' => 'Creat Wireframes', 'status' => 'completed', 'progress' => 100],
            ];

        return ApiResponse::success(
            data: [
                'to_do' => [
                    'count' => is_countable($todoData) ? count($todoData) : 4,
                    'items' => $todoData,
                ],
                'done' => [
                    'count' => is_countable($doneData) ? count($doneData) : 5,
                    'items' => $doneData,
                ],
            ]
        );
    }

    public function files(Request $request): JsonResponse
    {
        // Grid of 9 file cards matching Figma screen: "Design mockups By Ahmed Hassan 6.4 MB • 1h ago"
        $files = [];
        for ($i = 1; $i <= 9; $i++) {
            $files[] = [
                'id' => "file-{$i}",
                'title' => 'Design mockups',
                'author' => 'Ahmed Hassan',
                'size' => '6.4 MB',
                'file_type' => 'pdf',
                'time_ago' => '1h ago',
                'download_url' => url("/api/v1/profile/files/{$i}/download"),
            ];
        }

        return ApiResponse::success(
            data: $files
        );
    }
}
