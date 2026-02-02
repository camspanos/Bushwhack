<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\FishingLog;
use App\Models\UserLocation;
use App\Observers\FishingLogsObserver;
use App\Observers\UserLocationsObserver;
use App\Services\StubPaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the PaymentGateway interface to the stub implementation
        // Replace with real implementation (Stripe, Paddle, etc.) when ready
        $this->app->bind(PaymentGateway::class, StubPaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FishingLog::observe(FishingLogsObserver::class);
        UserLocation::observe(UserLocationsObserver::class);
    }
}
