<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Meetings;

use Illuminate\Foundation\Http\FormRequest;

final class UploadRecordingRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:mp4,webm,mov,mp3,wav,m4a', 'max:102400'],
        ];
    }
}
