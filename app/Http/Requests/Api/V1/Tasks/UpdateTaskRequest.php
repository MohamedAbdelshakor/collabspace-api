<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTaskRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', 'required', Rule::enum(TaskPriority::class)],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'reviewer_id' => ['nullable', 'string', 'exists:users,ulid'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['string', 'exists:users,ulid'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
