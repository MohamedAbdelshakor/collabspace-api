<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\GoogleAuthController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('v1.')->group(function (): void {
    // Health check
    Route::get('/health', HealthController::class)->name('health');

    // Auth routes
    Route::prefix('auth')->as('auth.')->group(function (): void {
        // Public guest endpoints
        Route::post('/register', RegisterController::class)->name('register');
        Route::post('/login', LoginController::class)->middleware('throttle:login')->name('login');
        Route::post('/google', GoogleAuthController::class)->middleware('throttle:login')->name('google');
        Route::post('/forgot-password', ForgotPasswordController::class)->middleware('throttle:6,1')->name('forgot-password');
        Route::post('/reset-password', ResetPasswordController::class)->middleware('throttle:6,1')->name('reset-password');
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');

        // Authenticated endpoints
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', MeController::class)->name('me');
            Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
            Route::post('/logout-all', [LogoutController::class, 'logoutAll'])->name('logout-all');
            Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
                ->middleware('throttle:6,1')
                ->name('verification.send');
        });
    });
});
