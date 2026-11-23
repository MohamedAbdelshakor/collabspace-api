<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\AuthResponseResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthenticateUserAction $action): JsonResponse
    {
        $result = $action->handle($request->validated());

        return ApiResponse::success(
            data: new AuthResponseResource($result),
            message: __('collabspace.login_success')
        );
    }
}
