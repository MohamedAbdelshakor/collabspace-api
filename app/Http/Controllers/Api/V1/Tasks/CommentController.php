<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tasks;

use App\Actions\Comments\CreateCommentAction;
use App\Actions\Comments\DeleteCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Comments\CreateCommentRequest;
use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CommentController extends Controller
{
    public function index(Request $request, Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        $comments = $task->comments()
            ->with('user')
            ->latest()
            ->paginate(30);

        return CommentResource::collection($comments);
    }

    public function store(CreateCommentRequest $request, Task $task, CreateCommentAction $action): JsonResponse
    {
        $this->authorize('view', $task);

        /** @var User $user */
        $user = $request->user();

        $comment = $action->handle(
            task: $task,
            user: $user,
            body: (string) $request->validated('body')
        );

        return ApiResponse::success(
            data: new CommentResource($comment),
            message: 'Comment added successfully.',
            status: 201
        );
    }

    public function destroy(Request $request, Comment $comment, DeleteCommentAction $action): JsonResponse
    {
        $this->authorize('delete', $comment);

        $action->handle($comment);

        return ApiResponse::success(
            data: null,
            message: 'Comment deleted successfully.'
        );
    }
}
