<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Chats;

use Illuminate\Foundation\Http\FormRequest;

final class MarkConversationReadRequest extends FormRequest
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
            'message_id' => ['nullable', 'string'],
        ];
    }
}
