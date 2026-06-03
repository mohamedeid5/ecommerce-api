<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Payment\PaymentProviderInterface;
use App\Services\Payment\MockPaymentProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PaymentProviderInterface::class,
            MockPaymentProvider::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
