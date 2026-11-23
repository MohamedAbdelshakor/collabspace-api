<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Comments;

use Illuminate\Foundation\Http\FormRequest;

final class CreateCommentRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
