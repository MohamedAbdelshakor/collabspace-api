<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Projects;

use Illuminate\Foundation\Http\FormRequest;

final class AddProjectMemberRequest extends FormRequest
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
            'user_id' => ['required', 'string', 'exists:users,ulid'],
            'department_id' => ['nullable', 'string', 'exists:departments,ulid'],
            'role_title' => ['nullable', 'string', 'max:100'],
            'is_leader' => ['nullable', 'boolean'],
        ];
    }
}
