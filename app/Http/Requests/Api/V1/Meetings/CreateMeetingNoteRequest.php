<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Meetings;

use Illuminate\Foundation\Http\FormRequest;

final class CreateMeetingNoteRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:10000'],
        ];
    }
}
