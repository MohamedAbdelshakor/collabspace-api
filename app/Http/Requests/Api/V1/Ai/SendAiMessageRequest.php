<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Ai;

use Illuminate\Foundation\Http\FormRequest;

final class SendAiMessageRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'max:50'],
        ];
    }
}
