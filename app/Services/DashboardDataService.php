<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\FishingLog;
use App\Models\User;
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
     * Check if we're using SQLite database.
     */
    protected function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    /**
     * Get SQL expression for extracting year from a date column.
     */
    protected function yearExpression(string $column): string
    {
        return $this->isSqlite()
            ? "strftime('%Y', $column)"
            : "YEAR($column)";
    }

    /**
     * Get SQL expression for extracting day of week from a date column.
     * Returns 0-6 for SQLite (Sunday=0) and 1-7 for MySQL (Sunday=1).
     */
    protected function dayOfWeekExpression(string $column): string
    {
        return $this->isSqlite()
            ? "strftime('%w', $column)"
            : "DAYOFWEEK($column)";
    }

    /**
     * Get SQL expression for formatting date.
     */
    protected function dateFormatExpression(string $column, string $format): string
    {
        if ($this->isSqlite()) {
            // Convert MySQL format to SQLite strftime format
            $sqliteFormat = str_replace(
                ['%Y-%m', '%Y-%u'],
                ['%Y-%m', '%Y-%W'],
                $format
            );
            return "strftime('$sqliteFormat', $column)";
        }
        return "DATE_FORMAT($column, '$format')";
    }

    /**
     * Get available years from fishing logs for a user.
     */
    public function getAvailableYears(int $userId): array
    {
        $yearExpr = $this->yearExpression('date');
        $years = FishingLog::where('user_id', $userId)
            ->selectRaw("DISTINCT $yearExpr as year")
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
        $query = FishingLog::where('fishing_logs.user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $query->whereYear('fishing_logs.date', $yearFilter);
        }
        return $query;
    }

    /**
     * Get SQL expression for weighted average size.
     * When avg_size is provided: (max_size * 1 + avg_size * (quantity - 1)) / quantity
     * When avg_size is NULL: count as 1 fish with max_size only
     *
     * @param string $prefix Table prefix (e.g., 'fishing_logs.')
     * @return array{sum: string, count: string} SQL expressions for sum and count
     */
    protected function weightedSizeExpressions(string $prefix = ''): array
    {
        $maxSize = $prefix . 'max_size';
        $avgSize = $prefix . 'avg_size';
        $quantity = $prefix . 'quantity';

        // Sum of all fish sizes (weighted)
        $sumExpr = "SUM(CASE WHEN {$avgSize} IS NOT NULL AND {$quantity} > 1 THEN {$maxSize} + {$avgSize} * ({$quantity} - 1) ELSE {$maxSize} END)";

        // Count of fish with known sizes
        $countExpr = "SUM(CASE WHEN {$avgSize} IS NOT NULL AND {$quantity} > 1 THEN {$quantity} ELSE 1 END)";

        return ['sum' => $sumExpr, 'count' => $countExpr];
    }

    /**
     * Get SQL expression for weighted average size as a single expression.
     *
     * @param string $prefix Table prefix (e.g., 'fishing_logs.')
     * @param string $alias Column alias for the result
     * @return string SQL expression
     */
    protected function weightedAvgSizeExpression(string $prefix = '', string $alias = 'weighted_avg_size'): string
    {
        $exprs = $this->weightedSizeExpressions($prefix);
        return "({$exprs['sum']}) / NULLIF({$exprs['count']}, 0) as {$alias}";
    }

    /**
     * Get SQL expression for weighted average weight.
     * When avg_weight is provided: (max_weight * 1 + avg_weight * (quantity - 1)) / quantity
     * When avg_weight is NULL: count as 1 fish with max_weight only
     *
     * @param string $prefix Table prefix (e.g., 'fishing_logs.')
     * @return array{sum: string, count: string} SQL expressions for sum and count
     */
    protected function weightedWeightExpressions(string $prefix = ''): array
    {
        $maxWeight = $prefix . 'max_weight';
        $avgWeight = $prefix . 'avg_weight';
        $quantity = $prefix . 'quantity';

        // Sum of all fish weights (weighted)
        $sumExpr = "SUM(CASE WHEN {$avgWeight} IS NOT NULL AND {$quantity} > 1 THEN {$maxWeight} + {$avgWeight} * ({$quantity} - 1) ELSE {$maxWeight} END)";

        // Count of fish with known weights
        $countExpr = "SUM(CASE WHEN {$avgWeight} IS NOT NULL AND {$quantity} > 1 THEN {$quantity} ELSE 1 END)";

        return ['sum' => $sumExpr, 'count' => $countExpr];
    }

    /**
     * Get SQL expression for weighted average weight as a single expression.
     *
     * @param string $prefix Table prefix (e.g., 'fishing_logs.')
     * @param string $alias Column alias for the result
     * @return string SQL expression
     */
    protected function weightedAvgWeightExpression(string $prefix = '', string $alias = 'weighted_avg_weight'): string
    {
        $exprs = $this->weightedWeightExpressions($prefix);
        return "({$exprs['sum']}) / NULLIF({$exprs['count']}, 0) as {$alias}";
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

        // Most species caught in a day - count distinct species per day
        $mostSpeciesInDay = (clone $baseQuery)
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.date', DB::raw('COUNT(DISTINCT user_fish.id) as species_count'))
            ->groupBy('fishing_logs.date')
            ->orderByDesc('species_count')
            ->limit(1)
            ->first();

        // Success rate
        $successRate = $daysFished > 0 ? round(($daysWithFish / $daysFished) * 100, 1) : 0;

        return [
            'daysFished' => $daysFished,
            'daysWithFish' => $daysWithFish,
            'daysSkunked' => $daysSkunked,
            'mostInDay' => $mostInDay,
            'mostSpeciesInDay' => $mostSpeciesInDay ? [
                'count' => $mostSpeciesInDay->species_count,
                'date' => $mostSpeciesInDay->date,
            ] : null,
            'successRate' => $successRate,
        ];
    }

    /**
     * Get favorite weekday statistics.
     */
    public function getFavoriteWeekday(Builder $baseQuery): ?array
    {
        // SQLite strftime('%w') returns 0-6 (Sunday=0)
        // MySQL DAYOFWEEK() returns 1-7 (Sunday=1)
        if ($this->isSqlite()) {
            $weekdayCase = "CASE strftime('%w', date)
                    WHEN '0' THEN 'Sunday'
                    WHEN '1' THEN 'Monday'
                    WHEN '2' THEN 'Tuesday'
                    WHEN '3' THEN 'Wednesday'
                    WHEN '4' THEN 'Thursday'
                    WHEN '5' THEN 'Friday'
                    WHEN '6' THEN 'Saturday'
                END";
        } else {
            $weekdayCase = "CASE DAYOFWEEK(date)
                    WHEN 1 THEN 'Sunday'
                    WHEN 2 THEN 'Monday'
                    WHEN 3 THEN 'Tuesday'
                    WHEN 4 THEN 'Wednesday'
                    WHEN 5 THEN 'Thursday'
                    WHEN 6 THEN 'Friday'
                    WHEN 7 THEN 'Saturday'
                END";
        }

        $weekdayData = (clone $baseQuery)
            ->select(
                DB::raw("$weekdayCase as weekday"),
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
            $groupBy = $this->isSqlite()
                ? "strftime('%Y-%m', date)"
                : "DATE_FORMAT(date, '%Y-%m')";
        } else {
            // For specific year, group by week to keep it manageable
            $groupBy = $this->isSqlite()
                ? "strftime('%Y-%W', date)"
                : "DATE_FORMAT(date, '%Y-%u')";
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

    /**
     * Get weather-based statistics.
     */
    public function getWeatherStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Best cloud cover
        $bestCloudCover = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.cloud')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_weather.cloud', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_weather.cloud')
            ->orderByDesc('total_caught')
            ->first();

        // Best wind condition
        $bestWindCondition = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.wind')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_weather.wind', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_weather.wind')
            ->orderByDesc('total_caught')
            ->first();

        // Catches by precipitation
        $catchesByPrecipitation = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.precipitation')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_weather.precipitation', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_weather.precipitation')
            ->orderByDesc('total_caught')
            ->get()
            ->map(fn($item) => [
                'precipitation' => $item->precipitation,
                'total_caught' => $item->total_caught ?? 0,
            ]);

        // Barometric pressure trend
        $bestBarometricPressure = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.barometric_pressure')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_weather.barometric_pressure', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_weather.barometric_pressure')
            ->orderByDesc('total_caught')
            ->first();

        return [
            'bestCloudCover' => $bestCloudCover ? [
                'cloud' => $bestCloudCover->cloud,
                'total' => $bestCloudCover->total_caught ?? 0,
            ] : null,
            'bestWindCondition' => $bestWindCondition ? [
                'wind' => $bestWindCondition->wind,
                'total' => $bestWindCondition->total_caught ?? 0,
            ] : null,
            'catchesByPrecipitation' => $catchesByPrecipitation,
            'bestBarometricPressure' => $bestBarometricPressure ? [
                'pressure' => $bestBarometricPressure->barometric_pressure,
                'total' => $bestBarometricPressure->total_caught ?? 0,
            ] : null,
        ];
    }

    /**
     * Get water condition statistics.
     */
    public function getWaterConditionStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Best water clarity
        $bestWaterClarity = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.clarity')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.clarity', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_water_conditions.clarity')
            ->orderByDesc('total_caught')
            ->first();

        // Catches by water level
        $catchesByWaterLevel = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.level')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.level', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_water_conditions.level')
            ->orderByDesc('total_caught')
            ->get()
            ->map(fn($item) => [
                'level' => $item->level,
                'total_caught' => $item->total_caught ?? 0,
            ]);

        // Best water speed
        $bestWaterSpeed = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.speed')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.speed', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_water_conditions.speed')
            ->orderByDesc('total_caught')
            ->first();

        // Best surface condition
        $bestSurfaceCondition = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.surface_condition')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.surface_condition', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_water_conditions.surface_condition')
            ->orderByDesc('total_caught')
            ->first();

        // Catches by tide
        $catchesByTide = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.tide')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.tide', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_water_conditions.tide')
            ->orderByDesc('total_caught')
            ->get()
            ->map(fn($item) => [
                'tide' => $item->tide,
                'total_caught' => $item->total_caught ?? 0,
            ]);

        return [
            'bestWaterClarity' => $bestWaterClarity ? [
                'clarity' => $bestWaterClarity->clarity,
                'total' => $bestWaterClarity->total_caught ?? 0,
            ] : null,
            'catchesByWaterLevel' => $catchesByWaterLevel,
            'bestWaterSpeed' => $bestWaterSpeed ? [
                'speed' => $bestWaterSpeed->speed,
                'total' => $bestWaterSpeed->total_caught ?? 0,
            ] : null,
            'bestSurfaceCondition' => $bestSurfaceCondition ? [
                'condition' => $bestSurfaceCondition->surface_condition,
                'total' => $bestSurfaceCondition->total_caught ?? 0,
            ] : null,
            'catchesByTide' => $catchesByTide,
        ];
    }

    /**
     * Get moon position statistics (Solunar Theory).
     */
    public function getMoonPositionStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Catches by moon position
        $catchesByMoonPosition = (clone $baseQuery)
            ->whereNotNull('moon_position')
            ->where('quantity', '>', 0)
            ->select('moon_position', DB::raw('SUM(quantity) as total_caught'))
            ->groupBy('moon_position')
            ->orderByDesc('total_caught')
            ->get()
            ->map(fn($item) => [
                'position' => $item->moon_position,
                'total_caught' => $item->total_caught ?? 0,
            ]);

        // Major vs Minor feeding windows
        $majorPositions = ['Overhead', 'Underfoot'];
        $minorPositions = ['Rising', 'Setting'];

        $majorFeeding = (clone $baseQuery)
            ->whereIn('moon_position', $majorPositions)
            ->where('quantity', '>', 0)
            ->sum('quantity') ?? 0;

        $minorFeeding = (clone $baseQuery)
            ->whereIn('moon_position', $minorPositions)
            ->where('quantity', '>', 0)
            ->sum('quantity') ?? 0;

        // Other catches (no moon position or unknown)
        $otherFeeding = (clone $baseQuery)
            ->where('quantity', '>', 0)
            ->where(function ($query) use ($majorPositions, $minorPositions) {
                $query->whereNull('moon_position')
                    ->orWhereNotIn('moon_position', array_merge($majorPositions, $minorPositions));
            })
            ->sum('quantity') ?? 0;

        // Best moon position for big fish
        $weightedAvgExpr = $this->weightedAvgSizeExpression('', 'weighted_avg_size');
        $bestMoonPositionForBigFish = (clone $baseQuery)
            ->whereNotNull('moon_position')
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->select('moon_position', DB::raw('MAX(max_size) as biggest_size'), DB::raw($weightedAvgExpr))
            ->groupBy('moon_position')
            ->orderByDesc('biggest_size')
            ->first();

        // Best moon phase for big fish
        $bestMoonPhaseForBigFish = (clone $baseQuery)
            ->whereNotNull('moon_phase')
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->select('moon_phase', DB::raw('MAX(max_size) as biggest_size'), DB::raw($weightedAvgExpr))
            ->groupBy('moon_phase')
            ->orderByDesc('biggest_size')
            ->first();

        return [
            'catchesByMoonPosition' => $catchesByMoonPosition,
            'majorVsMinorFeeding' => [
                'major' => $majorFeeding,
                'minor' => $minorFeeding,
                'other' => $otherFeeding,
            ],
            'bestMoonForBigFish' => [
                'position' => $bestMoonPositionForBigFish?->moon_position,
                'position_biggest_size' => $bestMoonPositionForBigFish?->biggest_size ?? 0,
                'position_avg_size' => round($bestMoonPositionForBigFish?->weighted_avg_size ?? 0, 2),
                'phase' => $bestMoonPhaseForBigFish?->moon_phase,
                'phase_biggest_size' => $bestMoonPhaseForBigFish?->biggest_size ?? 0,
                'phase_avg_size' => round($bestMoonPhaseForBigFish?->weighted_avg_size ?? 0, 2),
            ],
        ];
    }

    /**
     * Get weight-based statistics.
     */
    public function getWeightStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Heaviest catch
        $heaviestCatch = (clone $baseQuery)
            ->whereNotNull('max_weight')
            ->where('max_weight', '>', 0)
            ->orderByDesc('max_weight')
            ->with(['fish', 'location'])
            ->first();

        // Total weight caught and count of fish with weight data
        $weightStats = (clone $baseQuery)
            ->whereNotNull('max_weight')
            ->where('max_weight', '>', 0)
            ->selectRaw('SUM(max_weight) as total_weight, COUNT(*) as fish_count')
            ->first();

        $totalWeight = $weightStats->total_weight ?? 0;
        $fishWithWeightCount = $weightStats->fish_count ?? 0;
        $avgWeightPerFish = $fishWithWeightCount > 0 ? round($totalWeight / $fishWithWeightCount, 2) : 0;

        // Average weight by species
        $avgWeightBySpecies = (clone $baseQuery)
            ->whereNotNull('max_weight')
            ->whereNotNull('user_fish_id')
            ->where('max_weight', '>', 0)
            ->select('user_fish_id', DB::raw('AVG(max_weight) as avg_weight'), DB::raw('COUNT(*) as count'))
            ->groupBy('user_fish_id')
            ->having('count', '>=', 2)
            ->orderByDesc('avg_weight')
            ->with('fish')
            ->first();

        return [
            'heaviestCatch' => $heaviestCatch ? [
                'weight' => $heaviestCatch->max_weight,
                'species' => $heaviestCatch->fish?->species,
                'location' => $heaviestCatch->location?->name,
                'date' => $heaviestCatch->date,
            ] : null,
            'totalWeight' => round($totalWeight, 2),
            'fishWithWeightCount' => $fishWithWeightCount,
            'avgWeightPerFish' => $avgWeightPerFish,
            'avgWeightBySpecies' => $avgWeightBySpecies ? [
                'species' => $avgWeightBySpecies->fish?->species,
                'avg_weight' => round($avgWeightBySpecies->avg_weight ?? 0, 2),
                'count' => $avgWeightBySpecies->count ?? 0,
            ] : null,
        ];
    }

    /**
     * Get friend/social statistics.
     */
    public function getFriendStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Most productive buddy
        $mostProductiveBuddy = DB::table('fishing_log_user_friend')
            ->join('fishing_logs', 'fishing_log_user_friend.fishing_log_id', '=', 'fishing_logs.id')
            ->join('user_friends', 'fishing_log_user_friend.user_friend_id', '=', 'user_friends.id')
            ->where('fishing_logs.user_id', $userId)
            ->where('fishing_logs.quantity', '>', 0);

        if ($yearFilter !== 'lifetime') {
            $mostProductiveBuddy->whereYear('fishing_logs.date', $yearFilter);
        }

        $mostProductiveBuddy = $mostProductiveBuddy
            ->select('user_friends.name', DB::raw('SUM(fishing_logs.quantity) as total_caught'), DB::raw('COUNT(DISTINCT fishing_logs.date) as trips'))
            ->groupBy('user_friends.id', 'user_friends.name')
            ->orderByDesc('total_caught')
            ->first();

        // Solo vs Group fishing
        $soloQuery = (clone $baseQuery)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('fishing_log_user_friend')
                    ->whereColumn('fishing_log_user_friend.fishing_log_id', 'fishing_logs.id');
            })
            ->where('quantity', '>', 0);

        $groupQuery = (clone $baseQuery)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('fishing_log_user_friend')
                    ->whereColumn('fishing_log_user_friend.fishing_log_id', 'fishing_logs.id');
            })
            ->where('quantity', '>', 0);

        $soloCatches = $soloQuery->sum('quantity') ?? 0;
        $soloTrips = (clone $baseQuery)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('fishing_log_user_friend')
                    ->whereColumn('fishing_log_user_friend.fishing_log_id', 'fishing_logs.id');
            })
            ->distinct()->count('date');

        $groupCatches = $groupQuery->sum('quantity') ?? 0;
        $groupTrips = (clone $baseQuery)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('fishing_log_user_friend')
                    ->whereColumn('fishing_log_user_friend.fishing_log_id', 'fishing_logs.id');
            })
            ->distinct()->count('date');

        // Lucky charm friend (present during biggest catches)
        $luckyCharmFriend = DB::table('fishing_log_user_friend')
            ->join('fishing_logs', 'fishing_log_user_friend.fishing_log_id', '=', 'fishing_logs.id')
            ->join('user_friends', 'fishing_log_user_friend.user_friend_id', '=', 'user_friends.id')
            ->where('fishing_logs.user_id', $userId)
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0);

        if ($yearFilter !== 'lifetime') {
            $luckyCharmFriend->whereYear('fishing_logs.date', $yearFilter);
        }

        $weightedAvgExpr = $this->weightedAvgSizeExpression('fishing_logs.', 'weighted_avg_size');
        $luckyCharmFriend = $luckyCharmFriend
            ->select('user_friends.name', DB::raw('MAX(fishing_logs.max_size) as biggest_fish'), DB::raw($weightedAvgExpr))
            ->groupBy('user_friends.id', 'user_friends.name')
            ->orderByDesc('biggest_fish')
            ->first();

        return [
            'mostProductiveBuddy' => $mostProductiveBuddy ? [
                'name' => $mostProductiveBuddy->name,
                'total' => $mostProductiveBuddy->total_caught ?? 0,
                'trips' => $mostProductiveBuddy->trips ?? 0,
            ] : null,
            'soloVsGroup' => [
                'solo' => [
                    'catches' => $soloCatches,
                    'trips' => $soloTrips,
                    'avg' => $soloTrips > 0 ? round($soloCatches / $soloTrips, 1) : 0,
                ],
                'group' => [
                    'catches' => $groupCatches,
                    'trips' => $groupTrips,
                    'avg' => $groupTrips > 0 ? round($groupCatches / $groupTrips, 1) : 0,
                ],
            ],
            'luckyCharmFriend' => $luckyCharmFriend ? [
                'name' => $luckyCharmFriend->name,
                'biggest_fish' => $luckyCharmFriend->biggest_fish ?? 0,
                'avg_size' => round($luckyCharmFriend->weighted_avg_size ?? 0, 2),
            ] : null,
        ];
    }

    /**
     * Get rod and style statistics.
     */
    public function getRodStyleStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Most successful rod
        $mostSuccessfulRod = (clone $baseQuery)
            ->whereNotNull('user_rod_id')
            ->where('quantity', '>', 0)
            ->select('user_rod_id', DB::raw('SUM(quantity) as total_caught'), DB::raw('COUNT(DISTINCT date) as days_used'))
            ->groupBy('user_rod_id')
            ->orderByDesc('total_caught')
            ->with('rod')
            ->first();

        // Best rod for trophies
        $weightedAvgExpr = $this->weightedAvgSizeExpression('', 'weighted_avg_size');
        $bestRodForTrophies = (clone $baseQuery)
            ->whereNotNull('user_rod_id')
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->select('user_rod_id', DB::raw('MAX(max_size) as biggest_size'), DB::raw($weightedAvgExpr))
            ->groupBy('user_rod_id')
            ->orderByDesc('biggest_size')
            ->with('rod')
            ->first();

        // Catches by style
        $catchesByStyle = (clone $baseQuery)
            ->whereNotNull('style')
            ->where('quantity', '>', 0)
            ->select('style', DB::raw('SUM(quantity) as total_caught'))
            ->groupBy('style')
            ->orderByDesc('total_caught')
            ->get()
            ->map(fn($item) => [
                'style' => $item->style,
                'total_caught' => $item->total_caught ?? 0,
            ]);

        // Most successful style
        $mostSuccessfulStyle = (clone $baseQuery)
            ->whereNotNull('style')
            ->where('quantity', '>', 0)
            ->select('style', DB::raw('SUM(quantity) as total_caught'), DB::raw('COUNT(DISTINCT date) as days_used'))
            ->groupBy('style')
            ->orderByDesc('total_caught')
            ->first();

        return [
            'mostSuccessfulRod' => $mostSuccessfulRod ? [
                'name' => $mostSuccessfulRod->rod?->rod_name,
                'total' => $mostSuccessfulRod->total_caught ?? 0,
                'days' => $mostSuccessfulRod->days_used ?? 0,
            ] : null,
            'bestRodForTrophies' => $bestRodForTrophies ? [
                'name' => $bestRodForTrophies->rod?->rod_name,
                'biggest_size' => $bestRodForTrophies->biggest_size ?? 0,
                'avg_size' => round($bestRodForTrophies->weighted_avg_size ?? 0, 2),
            ] : null,
            'catchesByStyle' => $catchesByStyle,
            'mostSuccessfulStyle' => $mostSuccessfulStyle ? [
                'style' => $mostSuccessfulStyle->style,
                'total' => $mostSuccessfulStyle->total_caught ?? 0,
                'days' => $mostSuccessfulStyle->days_used ?? 0,
            ] : null,
        ];
    }

    /**
     * Get combined/golden conditions analysis.
     */
    public function getGoldenConditions(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Find the best combination of conditions for top catches
        // Get top 10 fishing days by catch count
        $topDays = (clone $baseQuery)
            ->where('quantity', '>', 0)
            ->select('date', DB::raw('SUM(quantity) as daily_total'))
            ->groupBy('date')
            ->orderByDesc('daily_total')
            ->limit(10)
            ->pluck('date');

        // Analyze conditions on those top days
        $topDayConditions = (clone $baseQuery)
            ->whereIn('date', $topDays)
            ->with(['weather', 'waterCondition'])
            ->get();

        // Helper function to get most common value from collection
        $getMostCommon = function ($collection) {
            $counts = $collection->filter()->countBy()->sortDesc();
            return $counts->isNotEmpty() ? $counts->keys()->first() : null;
        };

        // Helper function to get season from date
        $getSeason = function ($date) {
            $month = $date->month;
            if ($month >= 3 && $month <= 5) return 'Spring';
            if ($month >= 6 && $month <= 8) return 'Summer';
            if ($month >= 9 && $month <= 11) return 'Fall';
            return 'Winter';
        };

        // Build golden conditions array with all available data points
        $goldenConditions = [
            // Moon & Time
            'moon_position' => $getMostCommon($topDayConditions->pluck('moon_position')),
            'moon_phase' => $getMostCommon($topDayConditions->pluck('moon_phase')),
            'time_of_day' => $getMostCommon($topDayConditions->pluck('time_of_day')),
            'season' => $getMostCommon($topDayConditions->pluck('date')->map($getSeason)),
            // Weather
            'cloud' => $getMostCommon($topDayConditions->pluck('weather.cloud')),
            'wind' => $getMostCommon($topDayConditions->pluck('weather.wind')),
            'precipitation' => $getMostCommon($topDayConditions->pluck('weather.precipitation')),
            'barometric_pressure' => $getMostCommon($topDayConditions->pluck('weather.barometric_pressure')),
            'air_temperature' => $getMostCommon($topDayConditions->pluck('weather.temperature')),
            // Water
            'clarity' => $getMostCommon($topDayConditions->pluck('waterCondition.clarity')),
            'water_level' => $getMostCommon($topDayConditions->pluck('waterCondition.level')),
            'water_speed' => $getMostCommon($topDayConditions->pluck('waterCondition.speed')),
            'surface_condition' => $getMostCommon($topDayConditions->pluck('waterCondition.surface_condition')),
            'tide' => $getMostCommon($topDayConditions->pluck('waterCondition.tide')),
            'water_temperature' => $getMostCommon($topDayConditions->pluck('waterCondition.temperature')),
        ];

        // Golden conditions for biggest fish - analyze top 10 catches by size
        $topBigFishLogs = (clone $baseQuery)
            ->whereNotNull('avg_size')
            ->where('avg_size', '>', 0)
            ->with(['weather', 'waterCondition'])
            ->orderByDesc('avg_size')
            ->limit(10)
            ->get();

        // Build big fish conditions array with all available data points
        $bigFishConditions = [];
        if ($topBigFishLogs->isNotEmpty()) {
            $bigFishConditions = [
                // Moon & Time
                'moon_position' => $getMostCommon($topBigFishLogs->pluck('moon_position')),
                'moon_phase' => $getMostCommon($topBigFishLogs->pluck('moon_phase')),
                'time_of_day' => $getMostCommon($topBigFishLogs->pluck('time_of_day')),
                'season' => $getMostCommon($topBigFishLogs->pluck('date')->map($getSeason)),
                // Weather
                'cloud' => $getMostCommon($topBigFishLogs->pluck('weather.cloud')),
                'wind' => $getMostCommon($topBigFishLogs->pluck('weather.wind')),
                'precipitation' => $getMostCommon($topBigFishLogs->pluck('weather.precipitation')),
                'barometric_pressure' => $getMostCommon($topBigFishLogs->pluck('weather.barometric_pressure')),
                'air_temperature' => $getMostCommon($topBigFishLogs->pluck('weather.temperature')),
                // Water
                'clarity' => $getMostCommon($topBigFishLogs->pluck('waterCondition.clarity')),
                'water_level' => $getMostCommon($topBigFishLogs->pluck('waterCondition.level')),
                'water_speed' => $getMostCommon($topBigFishLogs->pluck('waterCondition.speed')),
                'surface_condition' => $getMostCommon($topBigFishLogs->pluck('waterCondition.surface_condition')),
                'tide' => $getMostCommon($topBigFishLogs->pluck('waterCondition.tide')),
                'water_temperature' => $getMostCommon($topBigFishLogs->pluck('waterCondition.temperature')),
            ];
        }

        return [
            'goldenConditions' => $goldenConditions,
            'bigFishConditions' => $bigFishConditions,
        ];
    }

    /**
     * Get time and pattern analysis statistics.
     */
    public function getTimePatternStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Best hour of day
        $bestHour = (clone $baseQuery)
            ->whereNotNull('fishing_logs.time')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(DB::raw('HOUR(fishing_logs.time) as hour'), DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy(DB::raw('HOUR(fishing_logs.time)'))
            ->orderByDesc('total_caught')
            ->first();

        // Morning vs Afternoon vs Evening
        $timeBlocks = (clone $baseQuery)
            ->whereNotNull('fishing_logs.time')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                DB::raw("CASE
                    WHEN HOUR(fishing_logs.time) >= 5 AND HOUR(fishing_logs.time) < 12 THEN 'Morning'
                    WHEN HOUR(fishing_logs.time) >= 12 AND HOUR(fishing_logs.time) < 17 THEN 'Afternoon'
                    WHEN HOUR(fishing_logs.time) >= 17 AND HOUR(fishing_logs.time) < 21 THEN 'Evening'
                    ELSE 'Night'
                END as time_block"),
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('time_block')
            ->orderByDesc('total_caught')
            ->get()
            ->mapWithKeys(fn($item) => [$item->time_block => $item->total_caught]);

        // Best day of month
        $bestDayOfMonth = (clone $baseQuery)
            ->where('fishing_logs.quantity', '>', 0)
            ->select(DB::raw('DAY(fishing_logs.date) as day_of_month'), DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy(DB::raw('DAY(fishing_logs.date)'))
            ->orderByDesc('total_caught')
            ->first();

        // Seasonal trends
        $seasonalTrends = (clone $baseQuery)
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                DB::raw("CASE
                    WHEN MONTH(fishing_logs.date) IN (3,4,5) THEN 'Spring'
                    WHEN MONTH(fishing_logs.date) IN (6,7,8) THEN 'Summer'
                    WHEN MONTH(fishing_logs.date) IN (9,10,11) THEN 'Fall'
                    ELSE 'Winter'
                END as season"),
                DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days_fished')
            )
            ->groupBy('season')
            ->get()
            ->mapWithKeys(fn($item) => [$item->season => [
                'total' => $item->total_caught,
                'days' => $item->days_fished,
                'avg' => $item->days_fished > 0 ? round($item->total_caught / $item->days_fished, 1) : 0,
            ]]);

        // Consecutive days streak (days with fish)
        $allDatesWithFish = (clone $baseQuery)
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.date')
            ->distinct()
            ->orderBy('fishing_logs.date')
            ->pluck('fishing_logs.date')
            ->map(fn($d) => \Carbon\Carbon::parse($d));

        $maxStreak = 0;
        $currentStreak = 1;
        for ($i = 1; $i < $allDatesWithFish->count(); $i++) {
            if ($allDatesWithFish[$i]->diffInDays($allDatesWithFish[$i - 1]) === 1) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }
        $maxStreak = max($maxStreak, $currentStreak);

        // Days since last skunk - respects year filter
        // A skunk day is a day where total catches = 0 (including NULL quantities)
        $lastSkunkDay = (clone $baseQuery)
            ->select('fishing_logs.date', DB::raw('COALESCE(SUM(fishing_logs.quantity), 0) as daily_total'))
            ->groupBy('fishing_logs.date')
            ->having('daily_total', '=', 0)
            ->orderByDesc('fishing_logs.date')
            ->first();
        $daysSinceSkunk = $lastSkunkDay ? (int) \Carbon\Carbon::parse($lastSkunkDay->date)->diffInDays(now()) : null;

        return [
            'bestHour' => $bestHour ? [
                'hour' => $bestHour->hour,
                'formatted' => \Carbon\Carbon::createFromTime($bestHour->hour)->format('g A'),
                'total' => $bestHour->total_caught,
            ] : null,
            'seasonalTrends' => $seasonalTrends,
            'consecutiveDaysStreak' => $maxStreak > 0 ? $maxStreak : null,
        ];
    }

    /**
     * Get location intelligence statistics.
     */
    public function getLocationStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Location variety
        $locationVariety = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_location_id')
            ->distinct()
            ->count('fishing_logs.user_location_id');

        // Most consistent spot (highest success rate)
        $locationStats = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_location_id')
            ->select(
                'fishing_logs.user_location_id',
                DB::raw('COUNT(DISTINCT fishing_logs.date) as total_days'),
                DB::raw('COUNT(DISTINCT CASE WHEN fishing_logs.quantity > 0 THEN fishing_logs.date END) as successful_days'),
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('fishing_logs.user_location_id')
            ->having('total_days', '>=', 2)
            ->get();

        $mostConsistent = $locationStats
            ->map(function ($item) {
                $item->success_rate = $item->total_days > 0 ? round(($item->successful_days / $item->total_days) * 100, 1) : 0;
                return $item;
            })
            ->sortByDesc('success_rate')
            ->first();

        $mostConsistentLocation = null;
        if ($mostConsistent) {
            $location = \App\Models\UserLocation::find($mostConsistent->user_location_id);
            $mostConsistentLocation = [
                'name' => $location?->name ?? 'Unknown',
                'success_rate' => $mostConsistent->success_rate,
                'days' => $mostConsistent->total_days,
            ];
        }

        // Underexplored spots (visited only 1-3 times in the selected period)
        $underexplored = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_location_id')
            ->select('fishing_logs.user_location_id', DB::raw('COUNT(DISTINCT fishing_logs.date) as visit_count'))
            ->groupBy('fishing_logs.user_location_id')
            ->havingRaw('COUNT(DISTINCT fishing_logs.date) BETWEEN 1 AND 3')
            ->get()
            ->count();

        // Best location by season
        $bestBySeasonQuery = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_location_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'fishing_logs.user_location_id',
                DB::raw("CASE
                    WHEN MONTH(fishing_logs.date) IN (3,4,5) THEN 'Spring'
                    WHEN MONTH(fishing_logs.date) IN (6,7,8) THEN 'Summer'
                    WHEN MONTH(fishing_logs.date) IN (9,10,11) THEN 'Fall'
                    ELSE 'Winter'
                END as season"),
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('fishing_logs.user_location_id', 'season')
            ->get();

        $bestBySeason = [];
        foreach (['Spring', 'Summer', 'Fall', 'Winter'] as $season) {
            $best = $bestBySeasonQuery->where('season', $season)->sortByDesc('total_caught')->first();
            if ($best) {
                $location = \App\Models\UserLocation::find($best->user_location_id);
                $bestBySeason[$season] = [
                    'name' => $location?->name ?? 'Unknown',
                    'total' => $best->total_caught,
                ];
            }
        }

        // New spot success rate
        $allLocations = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_location_id')
            ->select('fishing_logs.user_location_id', DB::raw('MIN(fishing_logs.date) as first_visit'), DB::raw('MAX(CASE WHEN fishing_logs.quantity > 0 THEN 1 ELSE 0 END) as had_success'))
            ->groupBy('fishing_logs.user_location_id')
            ->get();

        $newSpotSuccessRate = $allLocations->count() > 0
            ? round(($allLocations->where('had_success', 1)->count() / $allLocations->count()) * 100, 1)
            : null;

        return [
            'locationVariety' => $locationVariety,
            'mostConsistentSpot' => $mostConsistentLocation,
            'underexploredSpots' => $underexplored,
            'bestLocationBySeason' => $bestBySeason,
            'newSpotSuccessRate' => $newSpotSuccessRate,
        ];
    }

    /**
     * Get species deep dive statistics.
     */
    public function getSpeciesStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Species diversity score
        $speciesDiversity = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->distinct()
            ->count('fishing_logs.user_fish_id');

        // Rarest catch (species with lowest total quantity caught)
        $rarestCatches = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.user_fish_id', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('fishing_logs.user_fish_id')
            ->orderBy('total_caught')
            ->limit(3)
            ->get();

        $rarestCatchList = $rarestCatches->map(function ($item) {
            $fish = \App\Models\UserFish::find($item->user_fish_id);
            return [
                'name' => $fish?->species ?? 'Unknown',
                'count' => (int) $item->total_caught,
            ];
        });

        // Species streak (most consecutive days catching same species)
        // This is complex, so we'll simplify to "most caught species in a row"
        $speciesStreak = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.user_fish_id', DB::raw('COUNT(*) as times_caught'))
            ->groupBy('fishing_logs.user_fish_id')
            ->orderByDesc('times_caught')
            ->first();

        $speciesStreakData = null;
        if ($speciesStreak) {
            $fish = \App\Models\UserFish::find($speciesStreak->user_fish_id);
            $speciesStreakData = [
                'name' => $fish?->species ?? 'Unknown',
                'count' => $speciesStreak->times_caught,
            ];
        }

        // New species in selected period - respects year filter
        // For a specific year, compare species caught that year vs before that year
        // For lifetime, show total unique species count
        $selectedYear = $yearFilter !== 'lifetime' ? (int)$yearFilter : now()->year;

        if ($yearFilter === 'lifetime') {
            // For lifetime, just show total unique species count
            $newSpeciesInPeriod = FishingLog::where('fishing_logs.user_id', $userId)
                ->whereNotNull('fishing_logs.user_fish_id')
                ->where('fishing_logs.quantity', '>', 0)
                ->distinct('fishing_logs.user_fish_id')
                ->count('fishing_logs.user_fish_id');
        } else {
            // For a specific year, compare species caught that year vs before that year

            $speciesInPeriod = (clone $baseQuery)
                ->whereNotNull('fishing_logs.user_fish_id')
                ->where('fishing_logs.quantity', '>', 0)
                ->distinct()
                ->pluck('fishing_logs.user_fish_id');

            $speciesBefore = FishingLog::where('fishing_logs.user_id', $userId)
                ->whereYear('fishing_logs.date', '<', $selectedYear)
                ->whereNotNull('fishing_logs.user_fish_id')
                ->where('fishing_logs.quantity', '>', 0)
                ->distinct()
                ->pluck('fishing_logs.user_fish_id');

            $newSpeciesInPeriod = $speciesInPeriod->diff($speciesBefore)->count();
        }

        // Size improvement - respects year filter
        // Compares selected year vs previous year (or current vs last year for lifetime)
        $sizeImprovementItems = [];
        $comparisonYear = $yearFilter !== 'lifetime' ? (int)$yearFilter : now()->year;
        $previousComparisonYear = $comparisonYear - 1;

        // Get weighted average sizes for the comparison year
        $weightedAvgExpr = $this->weightedAvgSizeExpression('fishing_logs.', 'weighted_avg_size');
        $comparisonYearSizes = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $comparisonYear)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->select('fishing_logs.user_fish_id', DB::raw($weightedAvgExpr))
            ->groupBy('fishing_logs.user_fish_id')
            ->get();

        // Get weighted average sizes for the previous year
        $previousYearSizes = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $previousComparisonYear)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->select('fishing_logs.user_fish_id', DB::raw($weightedAvgExpr))
            ->groupBy('fishing_logs.user_fish_id')
            ->get()
            ->keyBy('user_fish_id');

        foreach ($comparisonYearSizes as $current) {
            $previous = $previousYearSizes->get($current->user_fish_id);
            if ($previous && $previous->weighted_avg_size > 0) {
                $fish = \App\Models\UserFish::find($current->user_fish_id);
                $improvement = round((($current->weighted_avg_size - $previous->weighted_avg_size) / $previous->weighted_avg_size) * 100, 1);
                $sizeImprovementItems[] = [
                    'name' => $fish?->species ?? 'Unknown',
                    'improvement' => $improvement,
                    'current_avg' => round($current->weighted_avg_size, 1),
                    'previous_avg' => round($previous->weighted_avg_size, 1),
                ];
            }
        }
        // Sort by improvement (highest first)
        usort($sizeImprovementItems, fn($a, $b) => $b['improvement'] <=> $a['improvement']);
        $sizeImprovementItems = array_slice($sizeImprovementItems, 0, 3);

        $sizeImprovement = [
            'items' => $sizeImprovementItems,
            'currentYear' => $comparisonYear,
            'previousYear' => $previousComparisonYear,
        ];

        return [
            'rarestCatches' => $rarestCatchList,
            'sizeImprovement' => $sizeImprovement,
        ];
    }

    /**
     * Get fly/lure pattern statistics.
     */
    public function getFlyPatternStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Fly rotation (variety)
        $flyRotation = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fly_id')
            ->distinct()
            ->count('fishing_logs.user_fly_id');

        // One-hit wonders (flies that caught fish once but never again)
        $oneHitWonders = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fly_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.user_fly_id', DB::raw('COUNT(*) as success_count'), DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('fishing_logs.user_fly_id')
            ->having('success_count', '=', 1)
            ->having('total_caught', '>=', 3)
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $fly = \App\Models\UserFly::find($item->user_fly_id);
                return [
                    'name' => $fly?->name ?? 'Unknown',
                    'caught' => $item->total_caught,
                ];
            });

        // Reliable producers (high success rate)
        $flyStats = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fly_id')
            ->select(
                'fishing_logs.user_fly_id',
                DB::raw('COUNT(*) as times_used'),
                DB::raw('SUM(CASE WHEN fishing_logs.quantity > 0 THEN 1 ELSE 0 END) as successful_uses'),
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('fishing_logs.user_fly_id')
            ->having('times_used', '>=', 3)
            ->get()
            ->map(function ($item) {
                $item->success_rate = round(($item->successful_uses / $item->times_used) * 100, 1);
                return $item;
            })
            ->sortByDesc('success_rate')
            ->take(3)
            ->map(function ($item) {
                $fly = \App\Models\UserFly::find($item->user_fly_id);
                return [
                    'name' => $fly?->name ?? 'Unknown',
                    'success_rate' => $item->success_rate,
                    'total' => $item->total_caught,
                ];
            })
            ->values();

        // Best fly by location
        $bestFlyByLocation = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fly_id')
            ->whereNotNull('fishing_logs.user_location_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.user_location_id', 'fishing_logs.user_fly_id', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('fishing_logs.user_location_id', 'fishing_logs.user_fly_id')
            ->get()
            ->groupBy('user_location_id')
            ->map(function ($group) {
                $best = $group->sortByDesc('total_caught')->first();
                $location = \App\Models\UserLocation::find($best->user_location_id);
                $fly = \App\Models\UserFly::find($best->user_fly_id);
                return [
                    'location' => $location?->name ?? 'Unknown',
                    'fly' => $fly?->name ?? 'Unknown',
                    'total' => $best->total_caught,
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // Best fly by species
        $bestFlyBySpecies = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fly_id')
            ->whereNotNull('fishing_logs.user_fish_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.user_fish_id', 'fishing_logs.user_fly_id', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('fishing_logs.user_fish_id', 'fishing_logs.user_fly_id')
            ->get()
            ->groupBy('user_fish_id')
            ->map(function ($group) {
                $best = $group->sortByDesc('total_caught')->first();
                $fish = \App\Models\UserFish::find($best->user_fish_id);
                $fly = \App\Models\UserFly::find($best->user_fly_id);
                return [
                    'species' => $fish?->species ?? 'Unknown',
                    'fly' => $fly?->name ?? 'Unknown',
                    'total' => $best->total_caught,
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        return [
            'flyRotation' => $flyRotation,
            'oneHitWonders' => $oneHitWonders,
            'bestFlyByLocation' => $bestFlyByLocation,
            'bestFlyBySpecies' => $bestFlyBySpecies,
        ];
    }

    /**
     * Get progress and goals statistics.
     */
    public function getProgressStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Determine comparison years based on filter
        $selectedYear = $yearFilter !== 'lifetime' ? (int)$yearFilter : now()->year;
        $previousYear = $selectedYear - 1;

        // Get today's month and day for year-to-date comparison
        $today = now();
        $currentMonth = $today->month;
        $currentDay = $today->day;

        // Determine if we're viewing a past year (compare full years) or current year (compare year-to-date)
        $isViewingPastYear = $selectedYear < now()->year;

        // This year stats - FULL YEAR for past years, or all data so far for current year
        $thisYearStats = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $selectedYear)
            ->select(
                DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days_fished'),
                DB::raw('MAX(fishing_logs.max_size) as biggest_fish')
            )
            ->first();

        // Last year stats - depends on whether we're viewing a past year or current year
        if ($isViewingPastYear) {
            // Viewing a past year: compare full year vs full previous year
            $lastYearComparisonStats = FishingLog::where('fishing_logs.user_id', $userId)
                ->whereYear('fishing_logs.date', $previousYear)
                ->select(
                    DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                    DB::raw('COUNT(DISTINCT fishing_logs.date) as days_fished'),
                    DB::raw('MAX(fishing_logs.max_size) as biggest_fish')
                )
                ->first();
        } else {
            // Viewing current year: compare year-to-date (same date range for apples-to-apples)
            $lastYearComparisonStats = FishingLog::where('fishing_logs.user_id', $userId)
                ->whereYear('fishing_logs.date', $previousYear)
                ->where(function($query) use ($currentMonth, $currentDay) {
                    $query->whereMonth('fishing_logs.date', '<', $currentMonth)
                          ->orWhere(function($q) use ($currentMonth, $currentDay) {
                              $q->whereMonth('fishing_logs.date', '=', $currentMonth)
                                ->whereDay('fishing_logs.date', '<=', $currentDay);
                          });
                })
                ->select(
                    DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                    DB::raw('COUNT(DISTINCT fishing_logs.date) as days_fished'),
                    DB::raw('MAX(fishing_logs.max_size) as biggest_fish')
                )
                ->first();
        }

        // Last year stats - FULL YEAR (for context, only shown when viewing current year)
        $lastYearFullStats = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $previousYear)
            ->select(
                DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days_fished'),
                DB::raw('MAX(fishing_logs.max_size) as biggest_fish')
            )
            ->first();

        $yoyComparison = [
            'thisYear' => [
                'catches' => $thisYearStats->total_caught ?? 0,
                'days' => $thisYearStats->days_fished ?? 0,
                'biggest' => $thisYearStats->biggest_fish,
                'year' => $selectedYear,
            ],
            'lastYearToDate' => [
                'catches' => $lastYearComparisonStats->total_caught ?? 0,
                'days' => $lastYearComparisonStats->days_fished ?? 0,
                'biggest' => $lastYearComparisonStats->biggest_fish,
                'year' => $previousYear,
                'asOfDate' => $isViewingPastYear ? 'Full Year' : $today->format('M j'),
            ],
            'lastYearFull' => [
                'catches' => $lastYearFullStats->total_caught ?? 0,
                'days' => $lastYearFullStats->days_fished ?? 0,
                'biggest' => $lastYearFullStats->biggest_fish,
                'year' => $previousYear,
            ],
            'catchChange' => $lastYearComparisonStats->total_caught > 0
                ? round((($thisYearStats->total_caught - $lastYearComparisonStats->total_caught) / $lastYearComparisonStats->total_caught) * 100, 1)
                : null,
            'isFullYearComparison' => $isViewingPastYear,
        ];

        // Personal bests timeline - respects year filter
        $personalBestsQuery = (clone $baseQuery)
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->orderByDesc('fishing_logs.max_size')
            ->limit(5)
            ->with(['fish', 'location']);

        $personalBests = $personalBestsQuery->get()
            ->map(fn($log) => [
                'size' => $log->max_size,
                'species' => $log->fish?->species ?? 'Unknown',
                'location' => $log->location?->name ?? 'Unknown',
                'date' => $log->date,
            ]);

        // Improvement rate (catches per trip trend) - respects year filter
        // Returns monthly data for bar chart display
        $monthlyAvgQuery = (clone $baseQuery)
            ->select(
                DB::raw('MONTH(fishing_logs.date) as month'),
                DB::raw('SUM(fishing_logs.quantity) as total'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days')
            )
            ->groupBy(DB::raw('MONTH(fishing_logs.date)'))
            ->orderBy('month');

        $monthlyAvg = $monthlyAvgQuery->get()
            ->map(fn($item) => [
                'month' => \Carbon\Carbon::create()->month($item->month)->format('M'),
                'monthNum' => $item->month,
                'avg' => $item->days > 0 ? round($item->total / $item->days, 1) : 0,
                'total' => $item->total,
                'days' => $item->days,
            ]);

        $improvementRate = null;
        $improvementPercent = null;
        if ($monthlyAvg->count() >= 2) {
            $first = $monthlyAvg->first()['avg'];
            $last = $monthlyAvg->last()['avg'];
            if ($first > 0) {
                $improvementPercent = round((($last - $first) / $first) * 100, 1);
            }
        }

        // Return both the monthly data and the overall percentage
        $improvementRate = [
            'monthlyData' => $monthlyAvg->values()->toArray(),
            'percent' => $improvementPercent,
            'maxAvg' => $monthlyAvg->max('avg') ?? 0,
        ];

        // Fishing frequency (days per month) - respects year filter
        $fishingFrequency = (clone $baseQuery)
            ->select(
                DB::raw('MONTH(fishing_logs.date) as month'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days')
            )
            ->groupBy(DB::raw('MONTH(fishing_logs.date)'))
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [\Carbon\Carbon::create()->month($item->month)->format('M') => $item->days]);

        // Average size trend (weighted average fish length per month) - respects year filter
        $weightedExprs = $this->weightedSizeExpressions('fishing_logs.');
        $monthlySizeQuery = (clone $baseQuery)
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->select(
                DB::raw('MONTH(fishing_logs.date) as month'),
                DB::raw("{$weightedExprs['sum']} as size_sum"),
                DB::raw("{$weightedExprs['count']} as fish_count"),
                DB::raw('COUNT(*) as log_count')
            )
            ->groupBy(DB::raw('MONTH(fishing_logs.date)'))
            ->orderBy('month');

        $monthlySizeData = $monthlySizeQuery->get()
            ->map(fn($item) => [
                'month' => \Carbon\Carbon::create()->month($item->month)->format('M'),
                'monthNum' => $item->month,
                'avg' => $item->fish_count > 0 ? round($item->size_sum / $item->fish_count, 1) : 0,
                'count' => $item->log_count,
            ]);

        $sizeTrendPercent = null;
        if ($monthlySizeData->count() >= 2) {
            $first = $monthlySizeData->first()['avg'];
            $last = $monthlySizeData->last()['avg'];
            if ($first > 0) {
                $sizeTrendPercent = round((($last - $first) / $first) * 100, 1);
            }
        }

        $avgSizeTrend = [
            'monthlyData' => $monthlySizeData->values()->toArray(),
            'percent' => $sizeTrendPercent,
            'maxAvg' => $monthlySizeData->max('avg') ?? 0,
        ];

        // Average weight trend (weighted average fish weight per month) - respects year filter
        $weightedWeightExprs = $this->weightedWeightExpressions('fishing_logs.');
        $monthlyWeightQuery = (clone $baseQuery)
            ->whereNotNull('fishing_logs.max_weight')
            ->where('fishing_logs.max_weight', '>', 0)
            ->select(
                DB::raw('MONTH(fishing_logs.date) as month'),
                DB::raw("{$weightedWeightExprs['sum']} as weight_sum"),
                DB::raw("{$weightedWeightExprs['count']} as fish_count"),
                DB::raw('COUNT(*) as log_count')
            )
            ->groupBy(DB::raw('MONTH(fishing_logs.date)'))
            ->orderBy('month');

        $monthlyWeightData = $monthlyWeightQuery->get()
            ->map(fn($item) => [
                'month' => \Carbon\Carbon::create()->month($item->month)->format('M'),
                'monthNum' => $item->month,
                'avg' => $item->fish_count > 0 ? round($item->weight_sum / $item->fish_count, 2) : 0,
                'count' => $item->log_count,
            ]);

        $weightTrendPercent = null;
        if ($monthlyWeightData->count() >= 2) {
            $first = $monthlyWeightData->first()['avg'];
            $last = $monthlyWeightData->last()['avg'];
            if ($first > 0) {
                $weightTrendPercent = round((($last - $first) / $first) * 100, 1);
            }
        }

        $avgWeightTrend = [
            'monthlyData' => $monthlyWeightData->values()->toArray(),
            'percent' => $weightTrendPercent,
            'maxAvg' => $monthlyWeightData->max('avg') ?? 0,
        ];

        return [
            'yoyComparison' => $yoyComparison,
            'improvementRate' => $improvementRate,
            'fishingFrequency' => $fishingFrequency,
            'avgSizeTrend' => $avgSizeTrend,
            'avgWeightTrend' => $avgWeightTrend,
        ];
    }

    /**
     * Get environmental combo statistics.
     */
    public function getEnvironmentalComboStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Wind + Cloud combo
        $windCloudCombo = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.wind')
            ->whereNotNull('user_weather.cloud')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'user_weather.wind',
                'user_weather.cloud',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('user_weather.wind', 'user_weather.cloud')
            ->orderByDesc('total_caught')
            ->first();

        // Moon + Time combo
        $moonTimeCombo = (clone $baseQuery)
            ->whereNotNull('fishing_logs.moon_position')
            ->whereNotNull('fishing_logs.time_of_day')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'fishing_logs.moon_position',
                'fishing_logs.time_of_day',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('fishing_logs.moon_position', 'fishing_logs.time_of_day')
            ->orderByDesc('total_caught')
            ->first();

        // Water + Weather combo
        $waterWeatherCombo = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.clarity')
            ->whereNotNull('user_weather.cloud')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'user_water_conditions.clarity',
                'user_weather.cloud',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('user_water_conditions.clarity', 'user_weather.cloud')
            ->orderByDesc('total_caught')
            ->first();

        return [
            'windCloudCombo' => $windCloudCombo ? [
                'wind' => $windCloudCombo->wind,
                'cloud' => $windCloudCombo->cloud,
                'total' => $windCloudCombo->total_caught,
            ] : null,
            'moonTimeCombo' => $moonTimeCombo ? [
                'moon' => $moonTimeCombo->moon_position,
                'time' => $moonTimeCombo->time_of_day,
                'total' => $moonTimeCombo->total_caught,
            ] : null,
            'waterWeatherCombo' => $waterWeatherCombo ? [
                'clarity' => $waterWeatherCombo->clarity,
                'cloud' => $waterWeatherCombo->cloud,
                'total' => $waterWeatherCombo->total_caught,
            ] : null,
        ];
    }

    /**
     * Get gamification statistics.
     */
    public function getGamificationStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Fishing score (weighted: quantity + size bonus + variety bonus)
        $totalCaught = (clone $baseQuery)->sum('fishing_logs.quantity') ?? 0;

        // Calculate weighted average size
        $weightedExprs = $this->weightedSizeExpressions('fishing_logs.');
        $sizeStats = (clone $baseQuery)
            ->whereNotNull('fishing_logs.max_size')
            ->selectRaw("{$weightedExprs['sum']} as size_sum, {$weightedExprs['count']} as fish_count")
            ->first();
        $avgSize = ($sizeStats && $sizeStats->fish_count > 0) ? ($sizeStats->size_sum / $sizeStats->fish_count) : 0;

        $speciesCount = (clone $baseQuery)->whereNotNull('fishing_logs.user_fish_id')->distinct()->count('fishing_logs.user_fish_id');
        $locationCount = (clone $baseQuery)->whereNotNull('fishing_logs.user_location_id')->distinct()->count('fishing_logs.user_location_id');

        $fishingScore = round(
            ($totalCaught * 10) +
            ($avgSize * 5) +
            ($speciesCount * 50) +
            ($locationCount * 25)
        );

        // Achievement badges - use the new badge system, filtered by year
        $user = User::find($userId);
        $earnedBadges = [];

        if ($user) {
            // Build the badges query
            $badgesQuery = $user->badges()
                ->orderBy('pivot_earned_at', 'desc');

            // Apply year filter to badges earned_at date (skip for 'lifetime')
            if ($yearFilter !== 'lifetime') {
                $badgesQuery->whereYear('user_badges.earned_at', $yearFilter);
            }

            $earnedBadges = $badgesQuery->get()
                ->map(function ($badge) {
                    return [
                        'name' => $badge->name,
                        'icon' => $badge->icon,
                        'description' => $badge->description,
                        'rarity' => $badge->rarity,
                        'category' => $badge->category,
                        'earned_at' => $badge->pivot->earned_at,
                    ];
                })
                ->toArray();
        }

        $badges = $earnedBadges;

        // Hot streak (current streak of successful days) - respects year filter
        $recentDaysQuery = (clone $baseQuery)
            ->select('fishing_logs.date', DB::raw('MAX(fishing_logs.quantity) as max_qty'))
            ->groupBy('fishing_logs.date')
            ->orderByDesc('fishing_logs.date')
            ->limit(30);

        $recentDays = $recentDaysQuery->get();

        $hotStreak = 0;
        foreach ($recentDays as $day) {
            if ($day->max_qty > 0) {
                $hotStreak++;
            } else {
                break;
            }
        }

        // Lucky number (most common daily catch total)
        $luckyNumber = DB::table(DB::raw("(
            SELECT fishing_logs.date, SUM(fishing_logs.quantity) as daily_total
            FROM fishing_logs
            WHERE fishing_logs.user_id = {$userId}
            AND fishing_logs.quantity > 0
            " . ($yearFilter !== 'lifetime' ? "AND YEAR(fishing_logs.date) = {$yearFilter}" : "") . "
            GROUP BY fishing_logs.date
        ) as daily_totals"))
            ->select('daily_total', DB::raw('COUNT(*) as occurrences'))
            ->groupBy('daily_total')
            ->orderByDesc('occurrences')
            ->first();

        return [
            'badges' => $badges,
            'hotStreak' => $hotStreak > 0 ? $hotStreak : null,
            'luckyNumber' => $luckyNumber ? [
                'number' => $luckyNumber->daily_total,
                'occurrences' => $luckyNumber->occurrences,
            ] : null,
        ];
    }

    /**
     * Get temperature-based statistics.
     */
    public function getTemperatureStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Best air temperature (most catches)
        $bestAirTemp = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.temperature')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_weather.temperature', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_weather.temperature')
            ->orderByDesc('total_caught')
            ->first();

        // Best water temperature (most catches)
        $bestWaterTemp = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.temperature')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.temperature', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_water_conditions.temperature')
            ->orderByDesc('total_caught')
            ->first();

        // Temperature sweet spot (air + water combo)
        $tempSweetSpot = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_weather.temperature')
            ->whereNotNull('user_water_conditions.temperature')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'user_weather.temperature as air_temp',
                'user_water_conditions.temperature as water_temp',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('user_weather.temperature', 'user_water_conditions.temperature')
            ->orderByDesc('total_caught')
            ->first();

        // Catches by air temperature (for chart)
        $catchesByAirTemp = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.temperature')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_weather.temperature', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_weather.temperature')
            ->orderBy('user_weather.temperature')
            ->get()
            ->map(fn($item) => [
                'temperature' => $item->temperature,
                'total_caught' => $item->total_caught ?? 0,
            ]);

        // Catches by water temperature (for chart)
        $catchesByWaterTemp = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.temperature')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.temperature', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_water_conditions.temperature')
            ->orderBy('user_water_conditions.temperature')
            ->get()
            ->map(fn($item) => [
                'temperature' => $item->temperature,
                'total_caught' => $item->total_caught ?? 0,
            ]);

        // Big fish temperature (air temp that produces biggest fish)
        $weightedExprs = $this->weightedSizeExpressions('fishing_logs.');
        $bigFishAirTemp = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.temperature')
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->select(
                'user_weather.temperature',
                DB::raw("{$weightedExprs['sum']} as size_sum"),
                DB::raw("{$weightedExprs['count']} as fish_count"),
                DB::raw('MAX(fishing_logs.max_size) as biggest_size')
            )
            ->groupBy('user_weather.temperature')
            ->orderByDesc('biggest_size')
            ->first();

        return [
            'tempSweetSpot' => $tempSweetSpot ? [
                'air_temp' => $tempSweetSpot->air_temp,
                'water_temp' => $tempSweetSpot->water_temp,
                'total' => $tempSweetSpot->total_caught,
            ] : null,
            'catchesByAirTemp' => $catchesByAirTemp->toArray(),
            'catchesByWaterTemp' => $catchesByWaterTemp->toArray(),
            'bigFishAirTemp' => $bigFishAirTemp ? [
                'temperature' => $bigFishAirTemp->temperature,
                'biggest_size' => $bigFishAirTemp->biggest_size,
                'avg_size' => $bigFishAirTemp->fish_count > 0 ? round($bigFishAirTemp->size_sum / $bigFishAirTemp->fish_count, 2) : 0,
            ] : null,
        ];
    }

    /**
     * Get fly size statistics.
     */
    public function getFlySizeStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Best fly size (most catches)
        $bestFlySize = (clone $baseQuery)
            ->join('user_flies', 'fishing_logs.user_fly_id', '=', 'user_flies.id')
            ->whereNotNull('user_flies.size')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_flies.size', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_flies.size')
            ->orderByDesc('total_caught')
            ->first();

        // Fly size by species (best fly size for top 5 species)
        $flySizeBySpecies = (clone $baseQuery)
            ->join('user_flies', 'fishing_logs.user_fly_id', '=', 'user_flies.id')
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->whereNotNull('user_flies.size')
            ->where('user_flies.size', '!=', '')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'user_fish.species',
                'user_flies.size',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('user_fish.species', 'user_flies.size')
            ->orderByDesc('total_caught')
            ->get()
            ->groupBy('species')
            ->map(fn($group) => $group->first())
            ->take(5)
            ->values()
            ->map(fn($item) => [
                'species' => $item->species,
                'size' => $item->size,
                'total' => $item->total_caught,
            ]);

        // Fly size by season
        $monthExpr = $this->isSqlite() ? "strftime('%m', fishing_logs.date)" : "MONTH(fishing_logs.date)";
        $flySizeBySeason = (clone $baseQuery)
            ->join('user_flies', 'fishing_logs.user_fly_id', '=', 'user_flies.id')
            ->whereNotNull('user_flies.size')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                DB::raw("CASE
                    WHEN $monthExpr IN (12, 1, 2) THEN 'Winter'
                    WHEN $monthExpr IN (3, 4, 5) THEN 'Spring'
                    WHEN $monthExpr IN (6, 7, 8) THEN 'Summer'
                    ELSE 'Fall'
                END as season"),
                'user_flies.size',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('season', 'user_flies.size')
            ->orderByDesc('total_caught')
            ->get()
            ->groupBy('season')
            ->map(fn($group) => $group->first())
            ->map(fn($item) => [
                'season' => $item->season,
                'size' => $item->size,
                'total' => $item->total_caught,
            ]);

        return [
            'bestFlySize' => $bestFlySize ? [
                'size' => $bestFlySize->size,
                'total' => $bestFlySize->total_caught,
            ] : null,
            'flySizeBySpecies' => $flySizeBySpecies->toArray(),
            'flySizeBySeason' => $flySizeBySeason->values()->toArray(),
        ];
    }

    /**
     * Get geographic statistics.
     */
    public function getGeographicStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Fishing radius (max distance from user's most common location)
        $locationsWithCoords = (clone $baseQuery)
            ->join('user_locations', 'fishing_logs.user_location_id', '=', 'user_locations.id')
            ->whereNotNull('user_locations.latitude')
            ->whereNotNull('user_locations.longitude')
            ->select('user_locations.latitude', 'user_locations.longitude', 'user_locations.name')
            ->distinct()
            ->get();

        $fishingRadius = null;
        if ($locationsWithCoords->count() > 1) {
            // Calculate max distance between any two locations (simplified)
            $maxDistance = 0;
            $coords = $locationsWithCoords->toArray();
            for ($i = 0; $i < count($coords); $i++) {
                for ($j = $i + 1; $j < count($coords); $j++) {
                    $distance = $this->haversineDistance(
                        $coords[$i]['latitude'], $coords[$i]['longitude'],
                        $coords[$j]['latitude'], $coords[$j]['longitude']
                    );
                    $maxDistance = max($maxDistance, $distance);
                }
            }
            $fishingRadius = round($maxDistance, 1);
        }

        // Catches by state (total fish count)
        $catchesByState = (clone $baseQuery)
            ->join('user_locations', 'fishing_logs.user_location_id', '=', 'user_locations.id')
            ->whereNotNull('user_locations.state')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_locations.state', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('user_locations.state')
            ->orderByDesc('total_caught')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'state' => $item->state,
                'total' => $item->total_caught,
            ]);

        // Catches by country (total fish count)
        $catchesByCountry = (clone $baseQuery)
            ->join('user_locations', 'fishing_logs.user_location_id', '=', 'user_locations.id')
            ->join('countries', 'user_locations.country_id', '=', 'countries.id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('countries.name as country', DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy('countries.name')
            ->orderByDesc('total_caught')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'country' => $item->country,
                'total' => $item->total_caught,
            ]);

        // Species by state (unique species count per state)
        $speciesByState = (clone $baseQuery)
            ->join('user_locations', 'fishing_logs.user_location_id', '=', 'user_locations.id')
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->whereNotNull('user_locations.state')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_locations.state', DB::raw('COUNT(DISTINCT user_fish.species) as species_count'))
            ->groupBy('user_locations.state')
            ->orderByDesc('species_count')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'state' => $item->state,
                'species_count' => $item->species_count,
            ]);

        // Species by country (unique species count per country)
        $speciesByCountry = (clone $baseQuery)
            ->join('user_locations', 'fishing_logs.user_location_id', '=', 'user_locations.id')
            ->join('countries', 'user_locations.country_id', '=', 'countries.id')
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('countries.name as country', DB::raw('COUNT(DISTINCT user_fish.species) as species_count'))
            ->groupBy('countries.name')
            ->orderByDesc('species_count')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'country' => $item->country,
                'species_count' => $item->species_count,
            ]);

        // Freshwater vs Saltwater (catches) - uses fish water_type (freshwater/saltwater)
        $freshwaterVsSaltwater = (clone $baseQuery)
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->whereNotNull('user_fish.water_type')
            ->where('user_fish.water_type', '!=', '')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(DB::raw('LOWER(user_fish.water_type) as water_type'), DB::raw('SUM(fishing_logs.quantity) as total_caught'))
            ->groupBy(DB::raw('LOWER(user_fish.water_type)'))
            ->get()
            ->mapWithKeys(fn($item) => [strtolower($item->water_type) => $item->total_caught]);

        // Species by Water Type (unique species count) - uses fish water_type (freshwater/saltwater)
        $speciesByWaterType = (clone $baseQuery)
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->whereNotNull('user_fish.water_type')
            ->where('user_fish.water_type', '!=', '')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(DB::raw('LOWER(user_fish.water_type) as water_type'), DB::raw('COUNT(DISTINCT user_fish.species) as species_count'))
            ->groupBy(DB::raw('LOWER(user_fish.water_type)'))
            ->get()
            ->mapWithKeys(fn($item) => [strtolower($item->water_type) => $item->species_count]);

        return [
            'fishingRadius' => $fishingRadius,
            'catchesByState' => $catchesByState->toArray(),
            'catchesByCountry' => $catchesByCountry->toArray(),
            'speciesByState' => $speciesByState->toArray(),
            'speciesByCountry' => $speciesByCountry->toArray(),
            'freshwaterVsSaltwater' => [
                'freshwater' => $freshwaterVsSaltwater['freshwater'] ?? 0,
                'saltwater' => $freshwaterVsSaltwater['saltwater'] ?? 0,
            ],
            'speciesByWaterType' => [
                'freshwater' => $speciesByWaterType['freshwater'] ?? 0,
                'saltwater' => $speciesByWaterType['saltwater'] ?? 0,
            ],
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in miles.
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 3959; // miles

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get additional analysis statistics.
     */
    public function getAdditionalAnalysisStats(int $userId, string $yearFilter): array
    {
        $baseQuery = $this->buildBaseQuery($userId, $yearFilter);

        // Weekend warrior (weekend vs weekday)
        $dayOfWeekExpr = $this->dayOfWeekExpression('fishing_logs.date');
        // MySQL: 1=Sunday, 7=Saturday; SQLite: 0=Sunday, 6=Saturday
        $weekendCondition = $this->isSqlite()
            ? "$dayOfWeekExpr IN (0, 6)"
            : "$dayOfWeekExpr IN (1, 7)";
        $weekdayCondition = $this->isSqlite()
            ? "$dayOfWeekExpr NOT IN (0, 6)"
            : "$dayOfWeekExpr NOT IN (1, 7)";

        $weekendCatches = (clone $baseQuery)
            ->whereRaw($weekendCondition)
            ->where('fishing_logs.quantity', '>', 0)
            ->sum('fishing_logs.quantity') ?? 0;

        $weekdayCatches = (clone $baseQuery)
            ->whereRaw($weekdayCondition)
            ->where('fishing_logs.quantity', '>', 0)
            ->sum('fishing_logs.quantity') ?? 0;

        $weekendDays = (clone $baseQuery)
            ->whereRaw($weekendCondition)
            ->distinct()
            ->count('fishing_logs.date');

        $weekdayDays = (clone $baseQuery)
            ->whereRaw($weekdayCondition)
            ->distinct()
            ->count('fishing_logs.date');

        // Monthly personal bests
        // Order descending to get most recent 12 months, then reverse to chronological order
        $monthExpr = $this->dateFormatExpression('fishing_logs.date', '%Y-%m');
        $monthlyPersonalBests = (clone $baseQuery)
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->select(
                DB::raw("$monthExpr as month"),
                DB::raw('MAX(fishing_logs.max_size) as biggest_size')
            )
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($item) => [
                'month' => $item->month,
                'biggest_size' => $item->biggest_size,
            ]);

        // Catch rate trend (fish per trip over time)
        // Order descending to get most recent 12 months, then reverse to chronological order
        $catchRateTrend = (clone $baseQuery)
            ->select(
                DB::raw("$monthExpr as month"),
                DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days_fished')
            )
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($item) => [
                'month' => $item->month,
                'catch_rate' => $item->days_fished > 0 ? round($item->total_caught / $item->days_fished, 2) : 0,
            ]);

        // Species by location (top species at each location)
        $speciesByLocation = (clone $baseQuery)
            ->join('user_locations', 'fishing_logs.user_location_id', '=', 'user_locations.id')
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'user_locations.name as location',
                'user_fish.species',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('user_locations.name', 'user_fish.species')
            ->orderByDesc('total_caught')
            ->get()
            ->groupBy('location')
            ->map(fn($group) => $group->first())
            ->take(6)
            ->values()
            ->map(fn($item) => [
                'location' => $item->location,
                'species' => $item->species,
                'total' => $item->total_caught,
            ]);

        // Fly color by conditions (best fly color for each cloud condition)
        $flyColorByConditions = (clone $baseQuery)
            ->join('user_flies', 'fishing_logs.user_fly_id', '=', 'user_flies.id')
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_flies.color')
            ->whereNotNull('user_weather.cloud')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'user_weather.cloud',
                'user_flies.color',
                DB::raw('SUM(fishing_logs.quantity) as total_caught')
            )
            ->groupBy('user_weather.cloud', 'user_flies.color')
            ->orderByDesc('total_caught')
            ->get()
            ->groupBy('cloud')
            ->map(fn($group) => $group->first())
            ->map(fn($item) => [
                'cloud' => $item->cloud,
                'color' => $item->color,
                'total' => $item->total_caught,
            ]);

        // Multi-species days (days where multiple species were caught)
        $multiSpeciesDays = (clone $baseQuery)
            ->join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.date', DB::raw('COUNT(DISTINCT user_fish.id) as species_count'))
            ->groupBy('fishing_logs.date')
            ->having('species_count', '>', 1)
            ->get()
            ->count();

        $totalDaysFished = (clone $baseQuery)->distinct()->count('fishing_logs.date');

        // Quantity vs Quality correlation
        // Compare days with high quantity vs days with big fish
        // Calculate weighted average: max_size applies to 1 fish, avg_size applies to remaining (quantity - 1)
        // Only count fish we have size data for
        $quantityVsQuality = (clone $baseQuery)
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.quantity', '>', 0)
            ->select(
                'fishing_logs.date',
                'fishing_logs.quantity',
                'fishing_logs.max_size',
                'fishing_logs.avg_size'
            )
            ->get()
            ->groupBy('date')
            ->map(function ($logs) {
                $totalQuantity = $logs->sum('quantity');
                $totalSize = 0;
                $fishWithSizeData = 0;

                foreach ($logs as $log) {
                    // max_size counts for 1 fish
                    $totalSize += $log->max_size;
                    $fishWithSizeData += 1;

                    // avg_size counts for remaining fish (quantity - 1) only if we have avg_size
                    if ($log->quantity > 1 && $log->avg_size) {
                        $totalSize += $log->avg_size * ($log->quantity - 1);
                        $fishWithSizeData += ($log->quantity - 1);
                    }
                }

                return [
                    'daily_quantity' => $totalQuantity,
                    'daily_avg_size' => $fishWithSizeData > 0 ? $totalSize / $fishWithSizeData : 0,
                ];
            });

        $highQuantityDays = $quantityVsQuality->where('daily_quantity', '>=', 5)->avg('daily_avg_size');
        $lowQuantityDays = $quantityVsQuality->where('daily_quantity', '<', 5)->avg('daily_avg_size');

        return [
            'weekendWarrior' => [
                'weekend' => ['catches' => $weekendCatches, 'days' => $weekendDays, 'avg' => $weekendDays > 0 ? round($weekendCatches / $weekendDays, 2) : 0],
                'weekday' => ['catches' => $weekdayCatches, 'days' => $weekdayDays, 'avg' => $weekdayDays > 0 ? round($weekdayCatches / $weekdayDays, 2) : 0],
            ],
            'monthlyPersonalBests' => $monthlyPersonalBests->toArray(),
            'catchRateTrend' => $catchRateTrend->toArray(),
            'speciesByLocation' => $speciesByLocation->toArray(),
            'flyColorByConditions' => $flyColorByConditions->values()->toArray(),
            'multiSpeciesDays' => [
                'count' => $multiSpeciesDays,
                'total_days' => $totalDaysFished,
                'percentage' => $totalDaysFished > 0 ? round(($multiSpeciesDays / $totalDaysFished) * 100, 1) : 0,
            ],
            'quantityVsQuality' => [
                'high_quantity_avg_size' => $highQuantityDays ? round($highQuantityDays, 2) : null,
                'low_quantity_avg_size' => $lowQuantityDays ? round($lowQuantityDays, 2) : null,
            ],
        ];
    }
}

