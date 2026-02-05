<?php

namespace App\Services;

use AurorasLive\SunCalc;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;

class MoonPositionCalculator
{
    /**
     * Moon position labels based on Solunar Theory.
     *
     * Solunar Theory positions with fishing relevance:
     * - Overhead: Major feeding window (~2-3 hours around upper transit)
     * - Rising: Minor feeding window (~45-75 min around moonrise)
     * - Setting: Minor feeding window (~45-75 min around moonset)
     * - Underfoot: Major feeding window (~2-3 hours around lower transit)
     * - Above Horizon: Transitional period between Rising/Setting and Overhead
     * - Below Horizon: Transitional period between Setting and Rising (via Underfoot)
     */
    public const POSITION_OVERHEAD = 'Overhead';
    public const POSITION_RISING = 'Rising';
    public const POSITION_SETTING = 'Setting';
    public const POSITION_UNDERFOOT = 'Underfoot';
    public const POSITION_ABOVE_HORIZON = 'Above Horizon';
    public const POSITION_BELOW_HORIZON = 'Below Horizon';

    /**
     * Time windows in seconds for position detection.
     */
    private const RISING_SETTING_WINDOW = 3600; // ±60 minutes from moonrise/moonset
    private const OVERHEAD_UNDERFOOT_WINDOW = 5400; // ±90 minutes from transit

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

        // Determine human-readable position label based on Solunar Theory
        $positionLabel = self::getPositionLabel($altitudeDegrees, $dateTime, $sunCalc, $latitude, $longitude);

        return [
            'altitude' => round($altitudeDegrees, 2),
            'position' => $positionLabel,
        ];
    }

    /**
     * Get human-readable position label based on Solunar Theory.
     *
     * The 6 positions are:
     * - Overhead: ±90 min from upper transit (major feeding window)
     * - Rising: ±60 min from moonrise (minor feeding window)
     * - Setting: ±60 min from moonset (minor feeding window)
     * - Underfoot: ±90 min from lower transit (major feeding window)
     * - Above Horizon: Moon is up but not in Rising/Setting/Overhead windows
     * - Below Horizon: Moon is down but not in Underfoot window
     *
     * @param float $altitudeDegrees Moon altitude in degrees
     * @param DateTime $dateTime Current date/time
     * @param SunCalc $sunCalc SunCalc instance for moon times
     * @param float $latitude Location latitude
     * @param float $longitude Location longitude
     * @return string
     */
    private static function getPositionLabel(float $altitudeDegrees, DateTime $dateTime, SunCalc $sunCalc, float $latitude, float $longitude): string
    {
        $currentTimestamp = $dateTime->getTimestamp();
        $moonTimes = $sunCalc->getMoonTimes();

        // Get moonrise and moonset times
        $moonrise = $moonTimes['moonrise'] ?? null;
        $moonset = $moonTimes['moonset'] ?? null;

        // Check for Rising (±60 min from moonrise)
        if ($moonrise) {
            $toRise = abs($currentTimestamp - $moonrise->getTimestamp());
            if ($toRise <= self::RISING_SETTING_WINDOW) {
                return self::POSITION_RISING;
            }
        }

        // Check for Setting (±60 min from moonset)
        if ($moonset) {
            $toSet = abs($currentTimestamp - $moonset->getTimestamp());
            if ($toSet <= self::RISING_SETTING_WINDOW) {
                return self::POSITION_SETTING;
            }
        }

        // Calculate upper and lower transit times
        // Upper transit (moon at highest point) is approximately halfway between rise and set
        // Lower transit (moon at lowest point/underfoot) is approximately 12 hours from upper transit
        $upperTransit = self::calculateUpperTransit($moonrise, $moonset, $dateTime);
        $lowerTransit = self::calculateLowerTransit($upperTransit, $dateTime);

        // Check for Overhead (±90 min from upper transit)
        if ($upperTransit) {
            $toUpperTransit = abs($currentTimestamp - $upperTransit->getTimestamp());
            if ($toUpperTransit <= self::OVERHEAD_UNDERFOOT_WINDOW) {
                return self::POSITION_OVERHEAD;
            }
        }

        // Check for Underfoot (±90 min from lower transit)
        if ($lowerTransit) {
            $toLowerTransit = abs($currentTimestamp - $lowerTransit->getTimestamp());
            if ($toLowerTransit <= self::OVERHEAD_UNDERFOOT_WINDOW) {
                return self::POSITION_UNDERFOOT;
            }
        }

        // If moon is above horizon, it's "Above Horizon" (transitional)
        if ($altitudeDegrees >= 0) {
            return self::POSITION_ABOVE_HORIZON;
        }

        // Moon is below horizon (transitional)
        return self::POSITION_BELOW_HORIZON;
    }

    /**
     * Calculate approximate upper transit time (moon at highest point).
     * Upper transit is approximately halfway between moonrise and moonset.
     *
     * @param DateTime|null $moonrise
     * @param DateTime|null $moonset
     * @param DateTime $dateTime Reference date/time
     * @return DateTime|null
     */
    private static function calculateUpperTransit(?DateTime $moonrise, ?DateTime $moonset, DateTime $dateTime): ?DateTime
    {
        if ($moonrise && $moonset) {
            $riseTimestamp = $moonrise->getTimestamp();
            $setTimestamp = $moonset->getTimestamp();

            // If moonset is before moonrise, add 24 hours to moonset
            if ($setTimestamp < $riseTimestamp) {
                $setTimestamp += 86400;
            }

            // Upper transit is midpoint between rise and set
            $transitTimestamp = (int)(($riseTimestamp + $setTimestamp) / 2);
            $transit = new DateTime();
            $transit->setTimestamp($transitTimestamp);
            $transit->setTimezone($dateTime->getTimezone());
            return $transit;
        }

        return null;
    }

    /**
     * Calculate approximate lower transit time (moon at lowest point/underfoot).
     * Lower transit is approximately 12 hours (half a lunar day) from upper transit.
     *
     * @param DateTime|null $upperTransit
     * @param DateTime $dateTime Reference date/time
     * @return DateTime|null
     */
    private static function calculateLowerTransit(?DateTime $upperTransit, DateTime $dateTime): ?DateTime
    {
        if ($upperTransit) {
            // Lower transit is approximately 12 hours and 25 minutes from upper transit
            // (half of the ~24h 50min lunar day)
            $lowerTransitTimestamp = $upperTransit->getTimestamp() + (12 * 3600 + 25 * 60);

            // Adjust to be within reasonable range of current time
            $currentTimestamp = $dateTime->getTimestamp();
            while ($lowerTransitTimestamp > $currentTimestamp + 43200) {
                $lowerTransitTimestamp -= 86400;
            }
            while ($lowerTransitTimestamp < $currentTimestamp - 43200) {
                $lowerTransitTimestamp += 86400;
            }

            $transit = new DateTime();
            $transit->setTimestamp($lowerTransitTimestamp);
            $transit->setTimezone($dateTime->getTimezone());
            return $transit;
        }

        return null;
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
            self::POSITION_RISING,
            self::POSITION_SETTING,
            self::POSITION_UNDERFOOT,
            self::POSITION_ABOVE_HORIZON,
            self::POSITION_BELOW_HORIZON,
        ];
    }
}

