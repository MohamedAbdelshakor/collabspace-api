<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    /**
     * Return a standardized success JSON response.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = Response::HTTP_OK,
        array $meta = [],
        array $links = []
    ): JsonResponse {
        $payload = [];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        if ($links !== []) {
            $payload['links'] = $links;
        }

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return new JsonResponse($payload, $status);
    }

    /**
     * Return a standardized created JSON response (201).
     */
    public static function created(mixed $data = null, ?string $message = 'Resource created successfully.'): JsonResponse
    {
        return self::success(data: $data, message: $message, status: Response::HTTP_CREATED);
    }

    /**
     * Return a 204 No Content response.
     */
    public static function noContent(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Return a 202 Accepted response for queued/background operations.
     */
    public static function accepted(mixed $data = null, ?string $message = 'Request accepted for processing.'): JsonResponse
    {
        return self::success(data: $data, message: $message, status: Response::HTTP_ACCEPTED);
    }

    /**
     * Return a paginated success response with standardized pagination meta.
     *
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        mixed $transformedData = null,
        ?string $message = null
    ): JsonResponse {
        $meta = [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];

        $links = [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];

        return self::success(
            data: $transformedData ?? $paginator->items(),
            message: $message,
            status: Response::HTTP_OK,
            meta: $meta,
            links: $links
        );
    }

    /**
     * Return a standardized error JSON response.
     *
     * @param  array<string, mixed>|null  $errors
     */
    public static function error(
        string $message,
        string $code = 'ERROR',
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'code' => $code,
        ];

        if ($errors !== null && $errors !== []) {
            $payload['errors'] = $errors;
        }

        return new JsonResponse($payload, $status);
    }
}
