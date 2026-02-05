<?php

namespace App\Services;

use AurorasLive\SunCalc;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;

class MoonPositionCalculator
{
    /**
     * Moon position labels based on altitude.
     */
    public const POSITION_OVERHEAD = 'Overhead';
    public const POSITION_HIGH = 'High';
    public const POSITION_RISING = 'Rising';
    public const POSITION_LOW = 'Low';
    public const POSITION_SETTING = 'Setting';
    public const POSITION_BELOW_HORIZON = 'Below Horizon';
    public const POSITION_UNDERFOOT = 'Underfoot';

    /**
     * Calculate moon position for a given date, time, and location.
     *
     * @param string|Carbon $date Date in Y-m-d format or Carbon instance
     * @param string|null $time Time in HH:MM format
     * @param float|null $latitude
     * @param float|null $longitude
     * @return array{altitude: float|null, position: string|null}
     */
    public static function calculate($date, ?string $time, ?float $latitude, ?float $longitude): array
    {
        // Need coordinates to calculate moon position
        if ($latitude === null || $longitude === null) {
            return ['altitude' => null, 'position' => null];
        }

        // Convert date to string if it's a Carbon instance
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        // Default to noon if no time provided
        $timeStr = $time ?? '12:00';

        // Create DateTime object with the correct timezone from the start
        try {
            // Get timezone based on longitude (approximate)
            $timezone = self::getTimezoneFromLongitude($longitude);
            $tz = new DateTimeZone($timezone);

            // Create DateTime directly in the target timezone
            // This interprets the time as being in that timezone
            $dateTime = new DateTime($date . ' ' . $timeStr, $tz);
        } catch (\Exception $e) {
            return ['altitude' => null, 'position' => null];
        }

        // Create SunCalc instance
        $sunCalc = new SunCalc($dateTime, $latitude, $longitude);

        // Get moon position
        $moonPosition = $sunCalc->getMoonPosition($dateTime);

        // Convert altitude from radians to degrees
        $altitudeDegrees = rad2deg($moonPosition->altitude);

        // Determine human-readable position label
        $positionLabel = self::getPositionLabel($altitudeDegrees, $dateTime, $sunCalc);

        return [
            'altitude' => round($altitudeDegrees, 2),
            'position' => $positionLabel,
        ];
    }

    /**
     * Get human-readable position label based on altitude.
     *
     * @param float $altitudeDegrees Moon altitude in degrees
     * @param DateTime $dateTime Current date/time
     * @param SunCalc $sunCalc SunCalc instance for moon times
     * @return string
     */
    private static function getPositionLabel(float $altitudeDegrees, DateTime $dateTime, SunCalc $sunCalc): string
    {
        // If moon is below horizon
        if ($altitudeDegrees < 0) {
            // If very low (opposite side of Earth), it's "underfoot"
            if ($altitudeDegrees < -45) {
                return self::POSITION_UNDERFOOT;
            }
            return self::POSITION_BELOW_HORIZON;
        }

        // Moon is above horizon
        // Check if it's near the horizon (rising or setting)
        if ($altitudeDegrees < 15) {
            // Try to determine if rising or setting based on moon times
            $moonTimes = $sunCalc->getMoonTimes();
            
            if (isset($moonTimes['moonrise']) && isset($moonTimes['moonset'])) {
                $moonrise = $moonTimes['moonrise'];
                $moonset = $moonTimes['moonset'];
                
                // Calculate time differences
                $toRise = abs($dateTime->getTimestamp() - $moonrise->getTimestamp());
                $toSet = abs($dateTime->getTimestamp() - $moonset->getTimestamp());
                
                // If closer to moonrise, it's rising; if closer to moonset, it's setting
                if ($toRise < $toSet && $toRise < 3600) { // Within 1 hour of moonrise
                    return self::POSITION_RISING;
                } elseif ($toSet < $toRise && $toSet < 3600) { // Within 1 hour of moonset
                    return self::POSITION_SETTING;
                }
            }
            
            return self::POSITION_LOW;
        }

        // High in the sky
        if ($altitudeDegrees >= 75) {
            return self::POSITION_OVERHEAD;
        }

        return self::POSITION_HIGH;
    }

    /**
     * Get approximate timezone from longitude.
     *
     * @param float $longitude
     * @return string
     */
    private static function getTimezoneFromLongitude(float $longitude): string
    {
        // Simple longitude-based timezone approximation for US locations
        if ($longitude > -67.5) {
            return 'America/New_York'; // Eastern
        } elseif ($longitude > -90) {
            return 'America/New_York'; // Eastern
        } elseif ($longitude > -105) {
            return 'America/Chicago'; // Central
        } elseif ($longitude > -120) {
            return 'America/Denver'; // Mountain
        } else {
            return 'America/Los_Angeles'; // Pacific
        }
    }

    /**
     * Get all possible moon position labels.
     *
     * @return array<string>
     */
    public static function getPositionLabels(): array
    {
        return [
            self::POSITION_OVERHEAD,
            self::POSITION_HIGH,
            self::POSITION_RISING,
            self::POSITION_LOW,
            self::POSITION_SETTING,
            self::POSITION_BELOW_HORIZON,
            self::POSITION_UNDERFOOT,
        ];
    }
}

