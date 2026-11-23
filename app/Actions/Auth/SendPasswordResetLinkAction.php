<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

final class SendPasswordResetLinkAction
{
    /**
     * @param  array{email: string}  $data
     */
    public function handle(array $data): string
    {
        Password::broker()->sendResetLink($data);

        // Always return generic message to prevent user enumeration
        return __('collabspace.forgot_password_sent');
    }
}
