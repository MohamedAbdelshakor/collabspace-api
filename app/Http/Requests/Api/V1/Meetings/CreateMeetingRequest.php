<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Meetings;

use Illuminate\Foundation\Http\FormRequest;

final class CreateMeetingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'project_id' => ['nullable', 'string', 'exists:projects,ulid'],
            'conversation_id' => ['nullable', 'string', 'exists:conversations,ulid'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'meeting_url' => ['nullable', 'url', 'max:500'],
            'attendee_ids' => ['nullable', 'array'],
            'attendee_ids.*' => ['string', 'exists:users,ulid'],
        ];
    }
}
