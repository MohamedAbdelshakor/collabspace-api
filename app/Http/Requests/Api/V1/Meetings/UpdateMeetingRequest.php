<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Meetings;

use App\Enums\MeetingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date', 'after:starts_at'],
            'meeting_url' => ['nullable', 'url', 'max:500'],
            'status' => ['sometimes', 'required', Rule::enum(MeetingStatus::class)],
            'attendee_ids' => ['nullable', 'array'],
            'attendee_ids.*' => ['string', 'exists:users,ulid'],
        ];
    }
}
