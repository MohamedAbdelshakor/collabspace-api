<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        $this->configurePasswordResetUrl();
        $this->configureEmailVerificationUrl();
        $this->configureRateLimiting();
        $this->registerModelObservers();
    }

    /**
     * Register Eloquent model observers.
     */
    private function registerModelObservers(): void
    {
        Project::observe(ProjectObserver::class);
        Task::observe(TaskObserver::class);
    }

    /**
     * Configure URL generator for API password reset links.
     */
    private function configurePasswordResetUrl(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            /** @var User $notifiable */
            $frontendUrl = (string) config('app.frontend_url', config('app.url'));

            return $frontendUrl.'/reset-password?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });
    }

    /**
     * Configure URL generator for API email verification links.
     */
    private function configureEmailVerificationUrl(): void
    {
        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            /** @var User $notifiable */
            $frontendUrl = (string) config('app.frontend_url', config('app.url'));
            $id = (string) $notifiable->ulid;
            $hash = sha1($notifiable->getEmailForVerification());

            return $frontendUrl.'/verify-email/'.$id.'/'.$hash;
        });
    }

    /**
     * Configure named rate limiters for the API.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('messages', function (Request $request): Limit {
            return Limit::perMinute(30)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('ai', function (Request $request): Limit {
            return Limit::perMinute(20)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('reports', function (Request $request): Limit {
            return Limit::perHour(5)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('uploads', function (Request $request): Limit {
            return Limit::perMinute(20)->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }
}
