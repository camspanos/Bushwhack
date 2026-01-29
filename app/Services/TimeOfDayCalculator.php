<?php

namespace App\Services;

use Carbon\Carbon;

class TimeOfDayCalculator
{
    /**
     * Calculate the time of day based on time, date, and location coordinates.
     *
     * Time periods (with coordinates):
     * - Dawn: 1 hour before sunrise to 1 hour after sunrise
     * - Morning: 1 hour after sunrise to 11:00 AM
     * - Midday: 11:00 AM to 2:00 PM
     * - Afternoon: 2:00 PM to 1 hour before sunset
     * - Dusk: 1 hour before sunset to 1 hour after sunset
     * - Night: 1 hour after sunset to 1 hour before sunrise
     *
     * @param string $time Time in HH:MM format
     * @param string $date Date in Y-m-d format
     * @param float|null $latitude
     * @param float|null $longitude
     * @return string|null
     */
    public static function calculate(?string $time, $date, ?float $latitude, ?float $longitude): ?string
    {
        if (!$time) {
            return null;
        }

        // Convert date to string if it's a Carbon instance
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        // Parse the time
        $dateTime = Carbon::parse($date . ' ' . $time);
        $timeInMinutes = $dateTime->hour * 60 + $dateTime->minute;

        // If we have coordinates, calculate sunrise/sunset
        if ($latitude !== null && $longitude !== null) {
            $timestamp = strtotime($date);
            $sunInfo = date_sun_info($timestamp, $latitude, $longitude);

            if ($sunInfo && isset($sunInfo['sunrise']) && isset($sunInfo['sunset'])) {
                // Get timezone from coordinates (approximate)
                // For US locations, we'll use a simple longitude-based approach
                // This is approximate but works for most cases
                $timezone = 'America/Denver'; // Default to Mountain Time
                if ($longitude > -90) {
                    $timezone = 'America/New_York'; // Eastern
                } elseif ($longitude > -105) {
                    $timezone = 'America/Chicago'; // Central
                } elseif ($longitude > -120) {
                    $timezone = 'America/Denver'; // Mountain
                } else {
                    $timezone = 'America/Los_Angeles'; // Pacific
                }

                // Create Carbon instances in the appropriate timezone
                $sunrise = Carbon::createFromTimestamp($sunInfo['sunrise'], 'UTC')->setTimezone($timezone);
                $sunset = Carbon::createFromTimestamp($sunInfo['sunset'], 'UTC')->setTimezone($timezone);

                $sunriseMinutes = $sunrise->hour * 60 + $sunrise->minute;
                $sunsetMinutes = $sunset->hour * 60 + $sunset->minute;

                $dawnStart = $sunriseMinutes - 60; // 1 hour before sunrise
                $dawnEnd = $sunriseMinutes + 60; // 1 hour after sunrise
                $duskStart = $sunsetMinutes - 60; // 1 hour before sunset
                $duskEnd = $sunsetMinutes + 60; // 1 hour after sunset

                // Dawn: 1 hour before sunrise to 1 hour after sunrise
                if ($timeInMinutes >= $dawnStart && $timeInMinutes < $dawnEnd) {
                    return 'Dawn';
                }

                // Morning: 1 hour after sunrise to 11:00 AM
                if ($timeInMinutes >= $dawnEnd && $timeInMinutes < 11 * 60) {
                    return 'Morning';
                }

                // Midday: 11:00 AM to 2:00 PM
                if ($timeInMinutes >= 11 * 60 && $timeInMinutes < 14 * 60) {
                    return 'Midday';
                }

                // Afternoon: 2:00 PM to 1 hour before sunset
                if ($timeInMinutes >= 14 * 60 && $timeInMinutes < $duskStart) {
                    return 'Afternoon';
                }

                // Dusk: 1 hour before sunset to 1 hour after sunset
                if ($timeInMinutes >= $duskStart && $timeInMinutes < $duskEnd) {
                    return 'Dusk';
                }

                // Night: Everything else
                return 'Night';
            }
        }

        // Fallback if no coordinates: use fixed times
        // Dawn: 5:00 AM - 7:00 AM
        if ($timeInMinutes >= 5 * 60 && $timeInMinutes < 7 * 60) {
            return 'Dawn';
        }

        // Morning: 7:00 AM - 11:00 AM
        if ($timeInMinutes >= 7 * 60 && $timeInMinutes < 11 * 60) {
            return 'Morning';
        }

        // Midday: 11:00 AM - 2:00 PM
        if ($timeInMinutes >= 11 * 60 && $timeInMinutes < 14 * 60) {
            return 'Midday';
        }

        // Afternoon: 2:00 PM - 6:00 PM
        if ($timeInMinutes >= 14 * 60 && $timeInMinutes < 18 * 60) {
            return 'Afternoon';
        }

        // Dusk: 6:00 PM - 8:00 PM
        if ($timeInMinutes >= 18 * 60 && $timeInMinutes < 20 * 60) {
            return 'Dusk';
        }

        // Night: 8:00 PM - 5:00 AM
        return 'Night';
    }
}

