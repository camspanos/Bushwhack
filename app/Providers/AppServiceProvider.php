<?php

namespace App\Providers;

use App\Models\FishingLog;
use App\Models\UserLocation;
use App\Observers\FishingLogsObserver;
use App\Observers\UserLocationsObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        FishingLog::observe(FishingLogsObserver::class);
        UserLocation::observe(UserLocationsObserver::class);
    }
}
