<?php

namespace App\Observers;

use App\Models\Location;
use App\Services\GeocodingService;

class LocationObserver
{
    /**
     * Handle the Location "saving" event.
     * This runs before the model is saved to the database (both create and update).
     */
    public function saving(Location $location): void
    {
        // Only geocode if city, state, or country changed (or it's a new record)
        if (!$location->exists || $location->isDirty(['city', 'state', 'country'])) {
            $this->geocodeLocation($location);
        }
    }

    /**
     * Geocode the location and set latitude/longitude.
     */
    private function geocodeLocation(Location $location): void
    {
        $coordinates = GeocodingService::getCoordinates(
            $location->city,
            $location->state,
            $location->country
        );

        $location->latitude = $coordinates['latitude'];
        $location->longitude = $coordinates['longitude'];
    }
}
