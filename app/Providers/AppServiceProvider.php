<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Services\AuthService;
use App\Services\FcmTokenService;
use App\Services\EmailVerificationService;
use App\Services\FirebaseAuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(FcmTokenService::class),
                $app->make(EmailVerificationService::class),
                $app->make(FirebaseAuthService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        Relation::morphMap([
            'product' => 'App\Models\Product',
            'media' => 'App\Models\Media',
            'service' => 'App\Models\Service',
            'user' => 'App\Models\User',
            'notification' => 'App\Models\Notification',
            'banner' => 'App\Models\Banner',
        ]);
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('App\Models\User::api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
