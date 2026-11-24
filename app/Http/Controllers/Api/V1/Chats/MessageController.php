<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chats;

use App\Actions\Chats\SendMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Chats\SendMessageRequest;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MessageController extends Controller
{
    public function index(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        $this->authorize('view', $conversation);

        $messages = Message::where('conversation_id', $conversation->id)
            ->with(['user', 'parent'])
            ->orderBy('id', 'asc')
            ->paginate((int) $request->input('per_page', 50));

        return MessageResource::collection($messages);
    }

    public function store(
        SendMessageRequest $request,
        Conversation $conversation,
        SendMessageAction $action
    ): JsonResponse {
        $this->authorize('sendMessage', $conversation);

        /** @var User $user */
        $user = $request->user();

        $parent = null;
        if ($parentUlid = $request->validated('parent_id')) {
            /** @var Message|null $parent */
            $parent = Message::where('conversation_id', $conversation->id)
                ->where('ulid', $parentUlid)
                ->first();
        }

        $message = $action->handle(
            conversation: $conversation,
            sender: $user,
            body: (string) $request->validated('body'),
            parent: $parent
        );

        return ApiResponse::success(
            data: new MessageResource($message),
            message: 'Message sent successfully.',
            status: 201
        );
    }

    public function destroy(Request $request, Message $message): JsonResponse
    {
        $this->authorize('delete', $message);

        $message->delete();

        return ApiResponse::success(
            data: null,
            message: 'Message deleted successfully.'
        );
    }
}
