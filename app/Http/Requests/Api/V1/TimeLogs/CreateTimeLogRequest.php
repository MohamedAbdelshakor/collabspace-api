<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\TimeLogs;

use Illuminate\Foundation\Http\FormRequest;

final class CreateTimeLogRequest extends FormRequest
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
            'minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'logged_on' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
