<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chats;

use App\Actions\Chats\CreateInstantMeetingAction;
use App\Actions\Chats\MarkConversationReadAction;
use App\Actions\Chats\StartDirectConversationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Chats\CreateInstantMeetingRequest;
use App\Http\Requests\Api\V1\Chats\MarkConversationReadRequest;
use App\Http\Requests\Api\V1\Chats\StartDirectConversationRequest;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Http\Resources\Api\V1\MeetingResource;
use App\Models\Conversation;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ConversationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $query = Conversation::whereHas('participants', function (Builder $q) use ($user): void {
            $q->where('users.id', $user->id);
        })
            ->with(['lastMessage.user', 'participants', 'project'])
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('participants', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $conversations = $query->paginate((int) $request->input('per_page', 25));

        return ConversationResource::collection($conversations);
    }

    public function direct(
        StartDirectConversationRequest $request,
        StartDirectConversationAction $action
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var User $recipient */
        $recipient = User::where('ulid', $request->validated('recipient_id'))->firstOrFail();

        $conversation = $action->handle($user, $recipient);

        return ApiResponse::success(
            data: new ConversationResource($conversation),
            message: 'Direct conversation initialized.',
            status: 200
        );
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->load(['participants', 'lastMessage.user', 'project']);

        return ApiResponse::success(
            data: new ConversationResource($conversation)
        );
    }

    public function read(
        MarkConversationReadRequest $request,
        Conversation $conversation,
        MarkConversationReadAction $action
    ): JsonResponse {
        $this->authorize('read', $conversation);

        /** @var User $user */
        $user = $request->user();

        $action->handle($conversation, $user, $request->validated('message_id'));

        return ApiResponse::success(
            data: null,
            message: 'Conversation marked as read.'
        );
    }

    public function instantMeeting(
        CreateInstantMeetingRequest $request,
        Conversation $conversation,
        CreateInstantMeetingAction $action
    ): JsonResponse {
        $this->authorize('view', $conversation);

        /** @var User $user */
        $user = $request->user();

        $meeting = $action->handle($conversation, $user, $request->validated('title'));

        return ApiResponse::success(
            data: new MeetingResource($meeting),
            message: 'Instant meeting started.',
            status: 201
        );
    }
}
