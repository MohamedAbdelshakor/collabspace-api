<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Projects;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProjectRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['sometimes', 'required', Rule::enum(ProjectType::class)],
            'priority' => ['sometimes', 'required', Rule::enum(ProjectPriority::class)],
            'status' => ['sometimes', 'required', Rule::enum(ProjectStatus::class)],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
