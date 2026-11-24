<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Chats;

use Illuminate\Foundation\Http\FormRequest;

final class SendMessageRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'string', 'exists:messages,ulid'],
        ];
    }
}
