<?php

namespace App\Services;

use App\Models\FishingLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Service class for shared dashboard data calculations.
 * Used by both DashboardController and PublicDashboardController.
 */
class DashboardDataService
{
    /**
     * Get available years from fishing logs for a user.
     */
    public function getAvailableYears(int $userId): array
    {
        $years = FishingLog::where('user_id', $userId)
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->map(fn($year) => (string) $year)
            ->values()
            ->toArray();

        // Always include current year even if no data exists for it
        $currentYear = (string) now()->year;
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        return $years;
    }

    /**
     * Build base query with year filter.
     */
    public function buildBaseQuery(int $userId, string $yearFilter): Builder
    {
        $query = FishingLog::where('user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $query->whereYear('date', $yearFilter);
        }
        return $query;
    }

    /**
     * Get year statistics (days fished, days with fish, etc.)
     */
    public function getYearStats(Builder $baseQuery): array
    {
        // Days fished (unique dates) - using SQL aggregation for performance
        $daysFished = (clone $baseQuery)->distinct()->count('date');

        // Days with fish caught - using SQL aggregation for performance
        $daysWithFish = (clone $baseQuery)
            ->where('quantity', '>', 0)
            ->distinct()
            ->count('date');

        // Days skunked (no fish caught) - calculated from the difference
        $daysSkunked = $daysFished - $daysWithFish;

        // Most caught in a day - using SQL aggregation
        $mostInDay = (clone $baseQuery)
            ->select(DB::raw('SUM(quantity) as daily_total'))
            ->groupBy('date')
            ->orderByDesc('daily_total')
            ->limit(1)
            ->value('daily_total') ?? 0;

        // Success rate
        $successRate = $daysFished > 0 ? round(($daysWithFish / $daysFished) * 100, 1) : 0;

        return [
            'daysFished' => $daysFished,
            'daysWithFish' => $daysWithFish,
            'daysSkunked' => $daysSkunked,
            'mostInDay' => $mostInDay,
            'successRate' => $successRate,
        ];
    }

    /**
     * Get favorite weekday statistics.
     */
    public function getFavoriteWeekday(Builder $baseQuery): ?array
    {
        $weekdayData = (clone $baseQuery)
            ->select(
                DB::raw("CASE DAYOFWEEK(date)
                    WHEN 1 THEN 'Sunday'
                    WHEN 2 THEN 'Monday'
                    WHEN 3 THEN 'Tuesday'
                    WHEN 4 THEN 'Wednesday'
                    WHEN 5 THEN 'Thursday'
                    WHEN 6 THEN 'Friday'
                    WHEN 7 THEN 'Saturday'
                END as weekday"),
                DB::raw('COUNT(*) as trip_count')
            )
            ->groupBy('weekday')
            ->orderByDesc('trip_count')
            ->first();

        return $weekdayData ? [
            'day' => $weekdayData->weekday,
            'count' => $weekdayData->trip_count,
        ] : null;
    }

    /**
     * Get fly statistics (consolidated query).
     */
    public function getFlyStats(int $userId, string $yearFilter): array
    {
        $flyStatsQuery = FishingLog::where('fishing_logs.user_id', $userId)
            ->join('user_flies', 'fishing_logs.user_fly_id', '=', 'user_flies.id')
            ->whereNotNull('fishing_logs.user_fly_id')
            ->where('user_flies.user_id', $userId);

        if ($yearFilter !== 'lifetime') {
            $flyStatsQuery->whereYear('fishing_logs.date', $yearFilter);
        }

        $flyStats = $flyStatsQuery
            ->select(
                'user_flies.id',
                'user_flies.name',
                'user_flies.type',
                'user_flies.color',
                DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                DB::raw('MAX(fishing_logs.max_size) as biggest_size'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days_used')
            )
            ->groupBy('user_flies.id', 'user_flies.name', 'user_flies.type', 'user_flies.color')
            ->get();

        // Extract most successful fly (by total caught)
        $mostSuccessfulFly = $flyStats
            ->where('total_caught', '>', 0)
            ->sortByDesc('total_caught')
            ->first();

        // Extract fly with biggest fish
        $biggestFishFly = $flyStats
            ->whereNotNull('biggest_size')
            ->where('biggest_size', '>', 0)
            ->sortByDesc('biggest_size')
            ->first();

        // Extract most successful fly type (aggregate by type)
        $flyTypeStats = $flyStats
            ->where('total_caught', '>', 0)
            ->whereNotNull('type')
            ->groupBy('type')
            ->map(function ($group) {
                return (object) [
                    'type' => $group->first()->type,
                    'total_caught' => $group->sum('total_caught'),
                    'days_used' => $group->sum('days_used'),
                ];
            })
            ->sortByDesc('total_caught');
        $mostSuccessfulFlyType = $flyTypeStats->first();

        // Extract most successful fly color (aggregate by color)
        $flyColorStats = $flyStats
            ->where('total_caught', '>', 0)
            ->whereNotNull('color')
            ->groupBy('color')
            ->map(function ($group) {
                return (object) [
                    'color' => $group->first()->color,
                    'total_caught' => $group->sum('total_caught'),
                    'days_used' => $group->sum('days_used'),
                ];
            })
            ->sortByDesc('total_caught');
        $mostSuccessfulFlyColor = $flyColorStats->first();

        return [
            'flyStats' => $flyStats,
            'mostSuccessfulFly' => $mostSuccessfulFly,
            'biggestFishFly' => $biggestFishFly,
            'mostSuccessfulFlyType' => $mostSuccessfulFlyType,
            'mostSuccessfulFlyColor' => $mostSuccessfulFlyColor,
        ];
    }

    /**
     * Get catches over time for chart display.
     */
    public function getCatchesOverTime(Builder $baseQuery, string $yearFilter): \Illuminate\Support\Collection
    {
        $catchesOverTimeQuery = (clone $baseQuery);

        if ($yearFilter === 'lifetime') {
            // For lifetime, show last 12 months grouped by month
            $catchesOverTimeQuery->where('date', '>=', now()->subMonths(12));
            $groupBy = "DATE_FORMAT(date, '%Y-%m')";
        } else {
            // For specific year, group by week to keep it manageable
            $groupBy = "DATE_FORMAT(date, '%Y-%u')";
        }

        return $catchesOverTimeQuery
            ->select(
                DB::raw("$groupBy as period"),
                DB::raw("MIN(date) as date"),
                DB::raw('SUM(quantity) as total')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => $item->total ?? 0,
                ];
            });
    }

    /**
     * Get streak statistics (current and longest streaks).
     */
    public function getStreakStats(int $userId, string $yearFilter): array
    {
        $streakQuery = FishingLog::where('user_id', $userId)
            ->where('quantity', '>', 0);
        if ($yearFilter !== 'lifetime') {
            $streakQuery->whereYear('date', $yearFilter);
        }

        $allTripsOrdered = $streakQuery
            ->orderBy('date')
            ->get()
            ->groupBy(function($log) {
                return Carbon::parse($log->date)->format('Y-m-d');
            });

        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 0;
        $lastDate = null;

        foreach ($allTripsOrdered as $date => $logs) {
            $currentDate = Carbon::parse($date);

            if ($lastDate === null) {
                $tempStreak = 1;
            } elseif ($lastDate->copy()->addDay()->isSameDay($currentDate)) {
                $tempStreak++;
            } else {
                $longestStreak = max($longestStreak, $tempStreak);
                $tempStreak = 1;
            }
            $lastDate = $currentDate;
        }
        $longestStreak = max($longestStreak, $tempStreak);

        // Calculate current streak (only counts if they fished today or yesterday)
        $today = Carbon::today();
        $recentTripsQuery = FishingLog::where('user_id', $userId)
            ->where('quantity', '>', 0);
        if ($yearFilter !== 'lifetime') {
            $recentTripsQuery->whereYear('date', $yearFilter);
        }

        $recentTrips = $recentTripsQuery
            ->orderByDesc('date')
            ->get()
            ->groupBy(function($log) {
                return Carbon::parse($log->date)->format('Y-m-d');
            });

        $expectedDate = $today;
        foreach ($recentTrips as $date => $logs) {
            $tripDate = Carbon::parse($date);

            if ($tripDate->isSameDay($expectedDate)) {
                $currentStreak++;
                $expectedDate = $expectedDate->subDay();
            } else {
                break;
            }
        }

        return [
            'currentStreak' => $currentStreak,
            'longestStreak' => $longestStreak,
        ];
    }
}

