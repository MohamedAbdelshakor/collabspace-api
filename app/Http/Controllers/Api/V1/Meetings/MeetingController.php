<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Meetings;

use App\Actions\Meetings\CreateMeetingAction;
use App\Actions\Meetings\RsvpMeetingAction;
use App\Actions\Meetings\UpdateMeetingAction;
use App\Actions\Meetings\UploadMeetingRecordingAction;
use App\Enums\MeetingAttendeeResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Meetings\CreateMeetingRequest;
use App\Http\Requests\Api\V1\Meetings\RsvpMeetingRequest;
use App\Http\Requests\Api\V1\Meetings\UpdateMeetingRequest;
use App\Http\Requests\Api\V1\Meetings\UploadRecordingRequest;
use App\Http\Resources\Api\V1\MeetingResource;
use App\Models\Meeting;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

final class MeetingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $query = Meeting::where(function (Builder $q) use ($user): void {
            if ($user->hasRole('admin')) {
                return;
            }

            $q->where('organizer_id', $user->id)
                ->orWhereHas('attendees', fn (Builder $att) => $att->where('users.id', $user->id))
                ->orWhereHas('project.members', fn (Builder $pm) => $pm->where('users.id', $user->id));
        })
            ->with(['organizer', 'project', 'attendees'])
            ->withCount(['attendees', 'transcriptSegments', 'notes', 'comments']);

        // Calendar date range filter
        if ($startDate = $request->input('start_date')) {
            $query->where('starts_at', '>=', Carbon::parse((string) $startDate));
        }

        if ($endDate = $request->input('end_date')) {
            $query->where('ends_at', '<=', Carbon::parse((string) $endDate));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($projectUlid = $request->input('project_id')) {
            $query->whereHas('project', fn (Builder $p) => $p->where('ulid', $projectUlid));
        }

        $meetings = $query->orderBy('starts_at', 'asc')->get();

        return MeetingResource::collection($meetings);
    }

    public function store(CreateMeetingRequest $request, CreateMeetingAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $meeting = $action->handle($user, $request->validated());

        return ApiResponse::success(
            data: new MeetingResource($meeting),
            message: 'Meeting scheduled successfully.',
            status: 201
        );
    }

    public function show(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorize('view', $meeting);

        $meeting->load(['organizer', 'project', 'attendees'])
            ->loadCount(['attendees', 'transcriptSegments', 'notes', 'comments']);

        return ApiResponse::success(
            data: new MeetingResource($meeting)
        );
    }

    public function update(UpdateMeetingRequest $request, Meeting $meeting, UpdateMeetingAction $action): JsonResponse
    {
        $this->authorize('update', $meeting);

        $updated = $action->handle($meeting, $request->validated());

        return ApiResponse::success(
            data: new MeetingResource($updated),
            message: 'Meeting updated successfully.'
        );
    }

    public function destroy(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorize('delete', $meeting);

        $meeting->delete();

        return ApiResponse::success(
            data: null,
            message: 'Meeting cancelled successfully.'
        );
    }

    public function rsvp(RsvpMeetingRequest $request, Meeting $meeting, RsvpMeetingAction $action): JsonResponse
    {
        $this->authorize('view', $meeting);

        /** @var User $user */
        $user = $request->user();
        $response = MeetingAttendeeResponse::from((string) $request->validated('response'));

        $action->handle($meeting, $user, $response);

        return ApiResponse::success(
            data: null,
            message: 'RSVP updated successfully.'
        );
    }

    public function uploadRecording(
        UploadRecordingRequest $request,
        Meeting $meeting,
        UploadMeetingRecordingAction $action
    ): JsonResponse {
        $this->authorize('uploadRecording', $meeting);

        /** @var UploadedFile $file */
        $file = $request->file('file');

        $action->handle($meeting, $file);

        return ApiResponse::success(
            data: new MeetingResource($meeting->refresh()->load(['organizer', 'attendees'])),
            message: 'Recording uploaded successfully.',
            status: 201
        );
    }
}
