<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Meetings;

use App\Actions\Meetings\CreateMeetingNoteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Meetings\CreateMeetingNoteRequest;
use App\Http\Resources\Api\V1\MeetingNoteResource;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MeetingNoteController extends Controller
{
    public function index(Request $request, Meeting $meeting): AnonymousResourceCollection
    {
        $this->authorize('view', $meeting);

        $notes = $meeting->notes()->with('user')->latest()->get();

        return MeetingNoteResource::collection($notes);
    }

    public function store(
        CreateMeetingNoteRequest $request,
        Meeting $meeting,
        CreateMeetingNoteAction $action
    ): JsonResponse {
        $this->authorize('view', $meeting);

        /** @var User $user */
        $user = $request->user();

        $note = $action->handle($meeting, $user, (string) $request->validated('body'));

        return ApiResponse::success(
            data: new MeetingNoteResource($note),
            message: 'Meeting note saved successfully.',
            status: 201
        );
    }

    public function destroy(Request $request, MeetingNote $note): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasRole('admin') && $note->user_id !== $user->id) {
            return ApiResponse::error(
                message: 'This action is unauthorized.',
                code: 'FORBIDDEN',
                status: 403
            );
        }

        $note->delete();

        return ApiResponse::success(
            data: null,
            message: 'Meeting note deleted successfully.'
        );
    }
}
