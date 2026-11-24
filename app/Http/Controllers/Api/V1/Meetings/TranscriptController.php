<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Meetings;

use App\Actions\Meetings\AddTranscriptSegmentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Meetings\CreateTranscriptSegmentRequest;
use App\Http\Resources\Api\V1\TranscriptSegmentResource;
use App\Models\Meeting;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TranscriptController extends Controller
{
    public function index(Request $request, Meeting $meeting): AnonymousResourceCollection
    {
        $this->authorize('view', $meeting);

        $query = $meeting->transcriptSegments()->with('speaker');

        if ($lang = $request->input('language')) {
            $query->where('language', $lang);
        }

        if ($search = $request->input('search')) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('text', 'like', "%{$search}%")
                    ->orWhereHas('speaker', fn (Builder $sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $segments = $query->orderBy('start_second', 'asc')->get();

        return TranscriptSegmentResource::collection($segments);
    }

    public function store(
        CreateTranscriptSegmentRequest $request,
        Meeting $meeting,
        AddTranscriptSegmentsAction $action
    ): JsonResponse {
        $this->authorize('update', $meeting);

        /** @var list<array{speaker_id?: string|null, start_second: int, text: string, language?: string|null}> $segments */
        $segments = $request->validated('segments');

        $action->handle($meeting, $segments);

        return ApiResponse::success(
            data: null,
            message: 'Transcript segments added successfully.',
            status: 201
        );
    }
}
