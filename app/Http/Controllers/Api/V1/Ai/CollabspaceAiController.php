<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ai\SendAiMessageRequest;
use App\Http\Requests\Api\V1\Ai\SubmitAiFeedbackRequest;
use App\Http\Resources\Api\V1\AiMessageResource;
use App\Models\AgentMessage;
use App\Models\User;
use App\Services\CollabspaceAiService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CollabspaceAiController extends Controller
{
    public function conversation(Request $request, CollabspaceAiService $aiService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $conversation = $aiService->getOrCreateConversation($user);
        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

        return ApiResponse::success(
            data: [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'messages' => AiMessageResource::collection($messages),
            ]
        );
    }

    public function messages(Request $request, CollabspaceAiService $aiService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $conversation = $aiService->getOrCreateConversation($user);
        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

        return ApiResponse::success(
            data: AiMessageResource::collection($messages)
        );
    }

    public function send(SendAiMessageRequest $request, CollabspaceAiService $aiService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $prompt = (string) $request->validated('message');
        $conversationId = $request->validated('conversation_id') ? (string) $request->validated('conversation_id') : null;

        $result = $aiService->send($user, $prompt, $conversationId);

        return ApiResponse::success(
            data: [
                'user_message' => new AiMessageResource($result['user_message']),
                'assistant_message' => new AiMessageResource($result['assistant_message']),
            ],
            status: 201
        );
    }

    public function feedback(SubmitAiFeedbackRequest $request, string $messageId, CollabspaceAiService $aiService): JsonResponse
    {
        /** @var AgentMessage|null $message */
        $message = AgentMessage::where('id', $messageId)->first();

        if (! $message) {
            return ApiResponse::error(
                message: 'Message not found.',
                code: 'NOT_FOUND',
                status: 404
            );
        }

        $rating = (string) $request->validated('rating');
        $comment = $request->validated('comment') ? (string) $request->validated('comment') : null;

        $updated = $aiService->submitFeedback($message, $rating, $comment);

        return ApiResponse::success(
            data: new AiMessageResource($updated),
            message: 'Feedback submitted successfully.'
        );
    }

    public function reset(Request $request, CollabspaceAiService $aiService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $conversation = $aiService->reset($user);

        return ApiResponse::success(
            data: [
                'id' => $conversation->id,
                'message' => 'Conversation reset successfully.',
                'messages' => AiMessageResource::collection($conversation->messages()->get()),
            ]
        );
    }
}
