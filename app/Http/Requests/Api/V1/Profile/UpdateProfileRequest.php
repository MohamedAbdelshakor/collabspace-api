<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Profile;

use App\Enums\AvailabilityStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class UpdateProfileRequest extends FormRequest
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
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone_number' => ['nullable', 'string', 'phone:AUTO,EG,US,INTERNATIONAL'],
            'phone_country' => ['nullable', 'string', 'max:5'],
            'availability_status' => ['nullable', 'string', new Enum(AvailabilityStatus::class)],
            'job_title' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'current_project_id' => ['nullable', 'string', 'exists:projects,ulid'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
