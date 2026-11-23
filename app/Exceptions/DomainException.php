<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class DomainException extends Exception
{
    public function __construct(
        string $message,
        protected string $errorCode = 'DOMAIN_ERROR',
        protected int $statusCode = 422
    ) {
        parent::__construct($message, $statusCode);
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error(
            message: $this->getMessage(),
            code: $this->errorCode,
            status: $this->statusCode
        );
    }
}
