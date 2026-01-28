<?php

namespace App\Observers;

use App\Models\UserLocation;
use App\Services\GeocodingService;

class UserLocationsObserver
{
    /**
     * Handle the Location "saving" event.
     * This runs before the model is saved to the database (both create and update).
     */
    public function saving(UserLocation $location): void
    {
        // Only auto-geocode if:
        // 1. Latitude/longitude are not manually set (both are null or empty)
        // 2. AND (it's a new record OR city/state/country_id changed)
        $hasManualCoordinates = !empty($location->latitude) && !empty($location->longitude);
        $locationFieldsChanged = !$location->exists || $location->isDirty(['city', 'state', 'country_id']);

        if (!$hasManualCoordinates && $locationFieldsChanged) {
            $this->geocodeLocation($location);
        }
    }

    /**
     * Geocode the location and set latitude/longitude.
     */
    private function geocodeLocation(UserLocation $location): void
    {
        // Get country name from relationship if country_id is set
        $countryName = null;
        if ($location->country_id) {
            // Load the country relationship if not already loaded
            if (!$location->relationLoaded('country')) {
                $location->load('country');
            }
            $countryName = $location->country?->name;
        }

        $coordinates = GeocodingService::getCoordinates(
            $location->city,
            $location->state,
            $countryName
        );

        $location->latitude = $coordinates['latitude'];
        $location->longitude = $coordinates['longitude'];
    }
}
