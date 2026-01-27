<?php

namespace App\Observers;

use App\Models\FishingLog;
use App\Services\TimeOfDayCalculator;
use Illuminate\Support\Facades\Cache;

class FishingLogsObserver
{
    /**
     * Handle the FishingLog "creating" event.
     * This runs before the model is saved to the database.
     */
    public function creating(FishingLog $fishingLog): void
    {
        $this->calculateTimeOfDay($fishingLog);
    }

    /**
     * Handle the FishingLog "updating" event.
     * This runs before the model is updated in the database.
     */
    public function updating(FishingLog $fishingLog): void
    {
        // Only recalculate if time, date, or location changed
        if ($fishingLog->isDirty(['time', 'date', 'user_location_id'])) {
            $this->calculateTimeOfDay($fishingLog);
        }
    }

    /**
     * Handle the FishingLog "saved" event.
     * This runs after the model is saved to the database (both create and update).
     */
    public function saved(FishingLog $fishingLog): void
    {
        $this->clearLeaderboardCache($fishingLog);
    }

    /**
     * Handle the FishingLog "deleted" event.
     */
    public function deleted(FishingLog $fishingLog): void
    {
        $this->clearLeaderboardCache($fishingLog);
    }

    /**
     * Calculate and set the time_of_day field.
     */
    private function calculateTimeOfDay(FishingLog $fishingLog): void
    {
        $latitude = null;
        $longitude = null;

        // Get coordinates from location if available
        if ($fishingLog->user_location_id) {
            // Load location if not already loaded
            if (!$fishingLog->relationLoaded('location')) {
                $fishingLog->load('location');
            }

            if ($fishingLog->location) {
                $latitude = $fishingLog->location->latitude;
                $longitude = $fishingLog->location->longitude;
            }
        }

        $fishingLog->time_of_day = TimeOfDayCalculator::calculate(
            $fishingLog->time,
            $fishingLog->date,
            $latitude,
            $longitude
        );
    }

    /**
     * Clear leaderboard and dashboard caches when a fishing log is created, updated, or deleted.
     * Clears cache for the month/year of the fishing log for both water types.
     */
    private function clearLeaderboardCache(FishingLog $fishingLog): void
    {
        $userId = $fishingLog->user_id;
        $date = $fishingLog->date;
        $year = $date->format('Y');

        // Clear dashboard cache for the user (for the year and lifetime)
        Cache::forget("dashboard_{$userId}_{$year}");
        Cache::forget("dashboard_{$userId}_lifetime");

        // Clear public dashboard cache for the user (for the year and lifetime)
        Cache::forget("public_dashboard_{$userId}_{$year}");
        Cache::forget("public_dashboard_{$userId}_lifetime");

        // Clear public profile pages cache (for the year and lifetime)
        Cache::forget("public_rods_{$userId}_{$year}");
        Cache::forget("public_rods_{$userId}_lifetime");
        Cache::forget("public_fish_{$userId}_{$year}");
        Cache::forget("public_fish_{$userId}_lifetime");
        Cache::forget("public_flies_{$userId}_{$year}");
        Cache::forget("public_flies_{$userId}_lifetime");

        // Clear user statistics pages cache (for the year and lifetime)
        Cache::forget("locations_stats_{$userId}_{$year}");
        Cache::forget("locations_stats_{$userId}_lifetime");
        Cache::forget("rods_stats_{$userId}_{$year}");
        Cache::forget("rods_stats_{$userId}_lifetime");
        Cache::forget("fish_stats_{$userId}_{$year}");
        Cache::forget("fish_stats_{$userId}_lifetime");
        Cache::forget("flies_stats_{$userId}_{$year}");
        Cache::forget("flies_stats_{$userId}_lifetime");
        Cache::forget("friends_stats_{$userId}_{$year}");
        Cache::forget("friends_stats_{$userId}_lifetime");

        // Only clear leaderboard cache if the user is premium (since leaderboard only shows premium users)
        if (!$fishingLog->user || !$fishingLog->user->is_premium) {
            return;
        }

        // Clear leaderboard cache for the specific month
        $month = $date->format('Y-m');
        Cache::forget("leaderboard_{$month}_all");
        Cache::forget("leaderboard_{$month}_freshwater");
        Cache::forget("leaderboard_{$month}_saltwater");

        // Clear leaderboard cache for the year
        Cache::forget("leaderboard_{$year}_all");
        Cache::forget("leaderboard_{$year}_freshwater");
        Cache::forget("leaderboard_{$year}_saltwater");
    }
}
