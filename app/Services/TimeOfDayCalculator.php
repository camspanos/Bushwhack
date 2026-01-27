<?php

namespace App\Services;

use Carbon\Carbon;

class TimeOfDayCalculator
{
    /**
     * Calculate the time of day based on time, date, and location coordinates.
     * 
     * Time periods:
     * - Pre-dawn: 1 hour before sunrise to sunrise
     * - Morning: Sunrise to 12:00 PM
     * - Midday: 12:00 PM to 3:00 PM
     * - Afternoon: 3:00 PM to sunset
     * - Evening: Sunset to 1 hour after sunset
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
                $sunrise = Carbon::createFromTimestamp($sunInfo['sunrise']);
                $sunset = Carbon::createFromTimestamp($sunInfo['sunset']);
                
                $sunriseMinutes = $sunrise->hour * 60 + $sunrise->minute;
                $sunsetMinutes = $sunset->hour * 60 + $sunset->minute;
                
                $preDawnStart = $sunriseMinutes - 60; // 1 hour before sunrise
                $eveningEnd = $sunsetMinutes + 60; // 1 hour after sunset
                
                // Pre-dawn: 1 hour before sunrise to sunrise
                if ($timeInMinutes >= $preDawnStart && $timeInMinutes < $sunriseMinutes) {
                    return 'Pre-dawn';
                }
                
                // Morning: Sunrise to 12:00 PM
                if ($timeInMinutes >= $sunriseMinutes && $timeInMinutes < 12 * 60) {
                    return 'Morning';
                }
                
                // Midday: 12:00 PM to 3:00 PM
                if ($timeInMinutes >= 12 * 60 && $timeInMinutes < 15 * 60) {
                    return 'Midday';
                }
                
                // Afternoon: 3:00 PM to sunset
                if ($timeInMinutes >= 15 * 60 && $timeInMinutes < $sunsetMinutes) {
                    return 'Afternoon';
                }
                
                // Evening: Sunset to 1 hour after sunset
                if ($timeInMinutes >= $sunsetMinutes && $timeInMinutes < $eveningEnd) {
                    return 'Evening';
                }
                
                // Night: Everything else
                return 'Night';
            }
        }

        // Fallback if no coordinates: use fixed times
        // Pre-dawn: 5:00 AM - 6:00 AM
        if ($timeInMinutes >= 5 * 60 && $timeInMinutes < 6 * 60) {
            return 'Pre-dawn';
        }
        
        // Morning: 6:00 AM - 12:00 PM
        if ($timeInMinutes >= 6 * 60 && $timeInMinutes < 12 * 60) {
            return 'Morning';
        }
        
        // Midday: 12:00 PM - 3:00 PM
        if ($timeInMinutes >= 12 * 60 && $timeInMinutes < 15 * 60) {
            return 'Midday';
        }
        
        // Afternoon: 3:00 PM - 6:00 PM
        if ($timeInMinutes >= 15 * 60 && $timeInMinutes < 18 * 60) {
            return 'Afternoon';
        }
        
        // Evening: 6:00 PM - 8:00 PM
        if ($timeInMinutes >= 18 * 60 && $timeInMinutes < 20 * 60) {
            return 'Evening';
        }
        
        // Night: 8:00 PM - 5:00 AM
        return 'Night';
    }
}

