<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmailVerificationController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(
                data: null,
                message: __('collabspace.email_already_verified')
            );
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(
            data: null,
            message: __('collabspace.verification_link_sent')
        );
    }

    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        /** @var User|null $user */
        $user = User::where('ulid', $id)->first();

        if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return ApiResponse::error(
                message: 'Invalid verification link.',
                code: 'INVALID_VERIFICATION_LINK',
                status: 403
            );
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(
                data: null,
                message: __('collabspace.email_already_verified')
            );
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return ApiResponse::success(
            data: null,
            message: __('collabspace.email_verified')
        );
    }
}
