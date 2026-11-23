<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Supported locales.
     *
     * @var list<string>
     */
    private const array SUPPORTED_LOCALES = ['en', 'ar'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $preferredLocale = $request->header('Accept-Language');

        if (is_string($preferredLocale)) {
            $locale = substr($preferredLocale, 0, 2);

            if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
                app()->setLocale($locale);
            }
        }

        $response = $next($request);

        $response->headers->set('Content-Language', app()->getLocale());

        return $response;
    }
}
