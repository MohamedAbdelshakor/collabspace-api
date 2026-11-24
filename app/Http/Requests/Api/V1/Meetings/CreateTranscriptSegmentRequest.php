<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Meetings;

use Illuminate\Foundation\Http\FormRequest;

final class CreateTranscriptSegmentRequest extends FormRequest
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
            'segments' => ['required', 'array', 'min:1'],
            'segments.*.speaker_id' => ['nullable', 'string', 'exists:users,ulid'],
            'segments.*.start_second' => ['required', 'integer', 'min:0'],
            'segments.*.text' => ['required', 'string', 'max:5000'],
            'segments.*.language' => ['nullable', 'string', 'max:10'],
        ];
    }
}
