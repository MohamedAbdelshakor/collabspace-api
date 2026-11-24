<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Meetings;

use App\Actions\Meetings\GenerateMeetingSummaryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MeetingSummaryResource;
use App\Models\Meeting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeetingSummaryController extends Controller
{
    public function show(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorize('view', $meeting);

        $summary = $meeting->summaries()->latest('id')->firstOrFail();

        return ApiResponse::success(
            data: new MeetingSummaryResource($summary)
        );
    }

    public function generate(
        Request $request,
        Meeting $meeting,
        GenerateMeetingSummaryAction $action
    ): JsonResponse {
        $this->authorize('generateSummary', $meeting);

        $language = (string) $request->input('language', 'en');
        $summary = $action->handle($meeting, $language);

        return ApiResponse::success(
            data: new MeetingSummaryResource($summary),
            message: 'Meeting summary generated successfully.'
        );
    }
}
