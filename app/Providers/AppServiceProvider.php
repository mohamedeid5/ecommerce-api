<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Payment\PaymentProviderInterface;
use App\Services\Payment\StripePaymentProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PaymentProviderInterface::class,
            StripePaymentProvider::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    protected function configureRateLimiters(): void
    {
        // Auth endpoints (strict - prevent brute force)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again in a minute.',
                    ], 429);
                });
        });

        // Payment initiation (strict - prevent abuse)
        RateLimiter::for('payments', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Webhooks (lenient - providers can retry)
        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        // Admin endpoints
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id);
        });

        // General API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
