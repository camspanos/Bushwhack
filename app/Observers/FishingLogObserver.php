<?php

namespace App\Observers;

use App\Models\FishingLog;
use App\Services\TimeOfDayCalculator;

class FishingLogObserver
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
        if ($fishingLog->isDirty(['time', 'date', 'location_id'])) {
            $this->calculateTimeOfDay($fishingLog);
        }
    }

    /**
     * Calculate and set the time_of_day field.
     */
    private function calculateTimeOfDay(FishingLog $fishingLog): void
    {
        $latitude = null;
        $longitude = null;

        // Get coordinates from location if available
        if ($fishingLog->location_id) {
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
}
