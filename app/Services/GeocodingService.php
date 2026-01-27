<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Get coordinates (latitude and longitude) for a location.
     *
     * @param string|null $city
     * @param string|null $state
     * @param string|null $country
     * @return array{latitude: float|null, longitude: float|null}
     */
    public static function getCoordinates(?string $city, ?string $state, ?string $country): array
    {
        // If no location data provided, return null coordinates
        if (empty($city) && empty($state) && empty($country)) {
            return ['latitude' => null, 'longitude' => null];
        }

        // Build the search query
        $query = self::buildQuery($city, $state, $country);

        try {
            // Use Nominatim (OpenStreetMap) geocoding service
            // Free to use with proper attribution and rate limiting
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'BushwhackFishingApp/1.0',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                
                return [
                    'latitude' => round((float) $data['lat'], 7),
                    'longitude' => round((float) $data['lon'], 7),
                ];
            }

            Log::info('Geocoding: No results found', ['query' => $query]);
            return ['latitude' => null, 'longitude' => null];

        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage(), [
                'query' => $query,
                'exception' => $e,
            ]);
            
            return ['latitude' => null, 'longitude' => null];
        }
    }

    /**
     * Build a search query from location components.
     *
     * @param string|null $city
     * @param string|null $state
     * @param string|null $country
     * @return string
     */
    private static function buildQuery(?string $city, ?string $state, ?string $country): string
    {
        $parts = array_filter([
            $city,
            $state,
            $country,
        ]);

        return implode(', ', $parts);
    }
}

