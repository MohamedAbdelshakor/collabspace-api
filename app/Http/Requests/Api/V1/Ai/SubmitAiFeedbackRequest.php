<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Ai;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitAiFeedbackRequest extends FormRequest
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
            'rating' => ['required', 'string', 'in:up,down'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }
}
