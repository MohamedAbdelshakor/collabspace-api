<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Meetings;

use App\Enums\MeetingAttendeeResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RsvpMeetingRequest extends FormRequest
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
            'response' => ['required', Rule::enum(MeetingAttendeeResponse::class)],
        ];
    }
}
