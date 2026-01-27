<?php

namespace App\Providers;

use App\Models\FishingLog;
use App\Models\Location;
use App\Observers\FishingLogObserver;
use App\Observers\LocationObserver;
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
        FishingLog::observe(FishingLogObserver::class);
        Location::observe(LocationObserver::class);
    }
}
