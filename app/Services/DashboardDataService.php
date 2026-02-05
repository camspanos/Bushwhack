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

        // Best moon position for big fish
        $bestMoonForBigFish = (clone $baseQuery)
            ->whereNotNull('moon_position')
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->select('moon_position', DB::raw('MAX(max_size) as biggest_size'), DB::raw('AVG(max_size) as avg_size'))
            ->groupBy('moon_position')
            ->orderByDesc('biggest_size')
            ->first();

        return [
            'catchesByMoonPosition' => $catchesByMoonPosition,
            'majorVsMinorFeeding' => [
                'major' => $majorFeeding,
                'minor' => $minorFeeding,
            ],
            'bestMoonForBigFish' => $bestMoonForBigFish ? [
                'position' => $bestMoonForBigFish->moon_position,
                'biggest_size' => $bestMoonForBigFish->biggest_size ?? 0,
                'avg_size' => round($bestMoonForBigFish->avg_size ?? 0, 2),
            ] : null,
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

        // Total weight caught
        $totalWeight = (clone $baseQuery)
            ->whereNotNull('max_weight')
            ->sum('max_weight') ?? 0;

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

        $luckyCharmFriend = $luckyCharmFriend
            ->select('user_friends.name', DB::raw('MAX(fishing_logs.max_size) as biggest_fish'), DB::raw('AVG(fishing_logs.max_size) as avg_size'))
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
                'avg_size' => round($luckyCharmFriend->avg_size ?? 0, 2),
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
        $bestRodForTrophies = (clone $baseQuery)
            ->whereNotNull('user_rod_id')
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->select('user_rod_id', DB::raw('MAX(max_size) as biggest_size'), DB::raw('AVG(max_size) as avg_size'))
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
                'avg_size' => round($bestRodForTrophies->avg_size ?? 0, 2),
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

        // Find most common conditions on best days
        $moonPositions = $topDayConditions->pluck('moon_position')->filter()->countBy()->sortDesc()->first();
        $moonPhases = $topDayConditions->pluck('moon_phase')->filter()->countBy()->sortDesc()->first();
        $timesOfDay = $topDayConditions->pluck('time_of_day')->filter()->countBy()->sortDesc()->first();
        $cloudCovers = $topDayConditions->pluck('weather.cloud')->filter()->countBy()->sortDesc()->first();
        $waterClarities = $topDayConditions->pluck('waterCondition.clarity')->filter()->countBy()->sortDesc()->first();

        // Best conditions summary - get the single best for each category
        $bestConditions = [];

        // Best moon position
        $bestMoonPosition = (clone $baseQuery)
            ->whereNotNull('moon_position')
            ->where('quantity', '>', 0)
            ->select('moon_position', DB::raw('SUM(quantity) as total'))
            ->groupBy('moon_position')
            ->orderByDesc('total')
            ->first();
        if ($bestMoonPosition) {
            $bestConditions['moon_position'] = $bestMoonPosition->moon_position;
        }

        // Best time of day
        $bestTimeOfDay = (clone $baseQuery)
            ->whereNotNull('time_of_day')
            ->where('quantity', '>', 0)
            ->select('time_of_day', DB::raw('SUM(quantity) as total'))
            ->groupBy('time_of_day')
            ->orderByDesc('total')
            ->first();
        if ($bestTimeOfDay) {
            $bestConditions['time_of_day'] = $bestTimeOfDay->time_of_day;
        }

        // Best cloud cover
        $bestCloud = (clone $baseQuery)
            ->join('user_weather', 'fishing_logs.user_weather_id', '=', 'user_weather.id')
            ->whereNotNull('user_weather.cloud')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_weather.cloud', DB::raw('SUM(fishing_logs.quantity) as total'))
            ->groupBy('user_weather.cloud')
            ->orderByDesc('total')
            ->first();
        if ($bestCloud) {
            $bestConditions['cloud'] = $bestCloud->cloud;
        }

        // Best water clarity
        $bestClarity = (clone $baseQuery)
            ->join('user_water_conditions', 'fishing_logs.user_water_condition_id', '=', 'user_water_conditions.id')
            ->whereNotNull('user_water_conditions.clarity')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('user_water_conditions.clarity', DB::raw('SUM(fishing_logs.quantity) as total'))
            ->groupBy('user_water_conditions.clarity')
            ->orderByDesc('total')
            ->first();
        if ($bestClarity) {
            $bestConditions['clarity'] = $bestClarity->clarity;
        }

        return [
            'goldenConditions' => [
                'moon_position' => $moonPositions ? array_key_first($topDayConditions->pluck('moon_position')->filter()->countBy()->sortDesc()->toArray()) : null,
                'moon_phase' => $moonPhases ? array_key_first($topDayConditions->pluck('moon_phase')->filter()->countBy()->sortDesc()->toArray()) : null,
                'time_of_day' => $timesOfDay ? array_key_first($topDayConditions->pluck('time_of_day')->filter()->countBy()->sortDesc()->toArray()) : null,
                'cloud' => $cloudCovers ? array_key_first($topDayConditions->pluck('weather.cloud')->filter()->countBy()->sortDesc()->toArray()) : null,
                'clarity' => $waterClarities ? array_key_first($topDayConditions->pluck('waterCondition.clarity')->filter()->countBy()->sortDesc()->toArray()) : null,
            ],
            'bestConditions' => $bestConditions,
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

        // Days since last skunk
        $lastSkunkDate = FishingLog::where('fishing_logs.user_id', $userId)
            ->where('fishing_logs.quantity', 0)
            ->orderByDesc('fishing_logs.date')
            ->value('fishing_logs.date');
        $daysSinceSkunk = $lastSkunkDate ? \Carbon\Carbon::parse($lastSkunkDate)->diffInDays(now()) : null;

        return [
            'bestHour' => $bestHour ? [
                'hour' => $bestHour->hour,
                'formatted' => \Carbon\Carbon::createFromTime($bestHour->hour)->format('g A'),
                'total' => $bestHour->total_caught,
            ] : null,
            'timeBlocks' => $timeBlocks,
            'bestDayOfMonth' => $bestDayOfMonth ? [
                'day' => $bestDayOfMonth->day_of_month,
                'total' => $bestDayOfMonth->total_caught,
            ] : null,
            'seasonalTrends' => $seasonalTrends,
            'consecutiveDaysStreak' => $maxStreak > 0 ? $maxStreak : null,
            'daysSinceSkunk' => $daysSinceSkunk,
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

        // Underexplored spots (not visited in 30+ days)
        $thirtyDaysAgo = now()->subDays(30)->toDateString();
        $underexplored = \App\Models\UserLocation::where('user_id', $userId)
            ->whereHas('fishingLogs')
            ->whereDoesntHave('fishingLogs', function ($q) use ($thirtyDaysAgo) {
                $q->where('date', '>=', $thirtyDaysAgo);
            })
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

        // Rarest catch (species caught only 1-2 times)
        $rarestCatches = (clone $baseQuery)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.user_fish_id', DB::raw('COUNT(*) as catch_count'))
            ->groupBy('fishing_logs.user_fish_id')
            ->having('catch_count', '<=', 2)
            ->orderBy('catch_count')
            ->limit(3)
            ->get();

        $rarestCatchList = $rarestCatches->map(function ($item) {
            $fish = \App\Models\UserFish::find($item->user_fish_id);
            return [
                'name' => $fish?->name ?? 'Unknown',
                'count' => $item->catch_count,
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
                'name' => $fish?->name ?? 'Unknown',
                'count' => $speciesStreak->times_caught,
            ];
        }

        // New species this year
        $currentYear = now()->year;
        $speciesThisYear = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $currentYear)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->distinct()
            ->pluck('fishing_logs.user_fish_id');

        $speciesBefore = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', '<', $currentYear)
            ->whereNotNull('fishing_logs.user_fish_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->distinct()
            ->pluck('fishing_logs.user_fish_id');

        $newSpeciesThisYear = $speciesThisYear->diff($speciesBefore)->count();

        // Size improvement (species where avg size is increasing)
        // Compare this year to previous years
        $sizeImprovement = [];
        if ($yearFilter === (string)$currentYear || $yearFilter === 'lifetime') {
            $thisYearSizes = FishingLog::where('fishing_logs.user_id', $userId)
                ->whereYear('fishing_logs.date', $currentYear)
                ->whereNotNull('fishing_logs.user_fish_id')
                ->whereNotNull('fishing_logs.max_size')
                ->where('fishing_logs.max_size', '>', 0)
                ->select('fishing_logs.user_fish_id', DB::raw('AVG(fishing_logs.max_size) as avg_size'))
                ->groupBy('fishing_logs.user_fish_id')
                ->get();

            $previousSizes = FishingLog::where('fishing_logs.user_id', $userId)
                ->whereYear('fishing_logs.date', '<', $currentYear)
                ->whereNotNull('fishing_logs.user_fish_id')
                ->whereNotNull('fishing_logs.max_size')
                ->where('fishing_logs.max_size', '>', 0)
                ->select('fishing_logs.user_fish_id', DB::raw('AVG(fishing_logs.max_size) as avg_size'))
                ->groupBy('fishing_logs.user_fish_id')
                ->get()
                ->keyBy('user_fish_id');

            foreach ($thisYearSizes as $current) {
                $previous = $previousSizes->get($current->user_fish_id);
                if ($previous && $current->avg_size > $previous->avg_size) {
                    $fish = \App\Models\UserFish::find($current->user_fish_id);
                    $improvement = round((($current->avg_size - $previous->avg_size) / $previous->avg_size) * 100, 1);
                    $sizeImprovement[] = [
                        'name' => $fish?->name ?? 'Unknown',
                        'improvement' => $improvement,
                        'current_avg' => round($current->avg_size, 1),
                    ];
                }
            }
            usort($sizeImprovement, fn($a, $b) => $b['improvement'] <=> $a['improvement']);
            $sizeImprovement = array_slice($sizeImprovement, 0, 3);
        }

        return [
            'speciesDiversity' => $speciesDiversity,
            'rarestCatches' => $rarestCatchList,
            'speciesStreak' => $speciesStreakData,
            'newSpeciesThisYear' => $newSpeciesThisYear,
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
                    'species' => $fish?->name ?? 'Unknown',
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
            'reliableProducers' => $flyStats,
            'bestFlyByLocation' => $bestFlyByLocation,
            'bestFlyBySpecies' => $bestFlyBySpecies,
        ];
    }

    /**
     * Get progress and goals statistics.
     */
    public function getProgressStats(int $userId, string $yearFilter): array
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Year-over-year comparison
        $thisYearStats = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $currentYear)
            ->select(
                DB::raw('SUM(fishing_logs.quantity) as total_caught'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days_fished'),
                DB::raw('MAX(fishing_logs.max_size) as biggest_fish')
            )
            ->first();

        $lastYearStats = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $lastYear)
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
            ],
            'lastYear' => [
                'catches' => $lastYearStats->total_caught ?? 0,
                'days' => $lastYearStats->days_fished ?? 0,
                'biggest' => $lastYearStats->biggest_fish,
            ],
            'catchChange' => $lastYearStats->total_caught > 0
                ? round((($thisYearStats->total_caught - $lastYearStats->total_caught) / $lastYearStats->total_caught) * 100, 1)
                : null,
        ];

        // Personal bests timeline
        $personalBests = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->orderByDesc('fishing_logs.max_size')
            ->limit(5)
            ->with(['fish', 'location'])
            ->get()
            ->map(fn($log) => [
                'size' => $log->max_size,
                'species' => $log->fish?->name ?? 'Unknown',
                'location' => $log->location?->name ?? 'Unknown',
                'date' => $log->date,
            ]);

        // Improvement rate (catches per trip trend)
        $monthlyAvg = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $currentYear)
            ->select(
                DB::raw('MONTH(fishing_logs.date) as month'),
                DB::raw('SUM(fishing_logs.quantity) as total'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days')
            )
            ->groupBy(DB::raw('MONTH(fishing_logs.date)'))
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'month' => $item->month,
                'avg' => $item->days > 0 ? round($item->total / $item->days, 1) : 0,
            ]);

        $improvementRate = null;
        if ($monthlyAvg->count() >= 2) {
            $first = $monthlyAvg->first()['avg'];
            $last = $monthlyAvg->last()['avg'];
            if ($first > 0) {
                $improvementRate = round((($last - $first) / $first) * 100, 1);
            }
        }

        // Fishing frequency (days per month)
        $fishingFrequency = FishingLog::where('fishing_logs.user_id', $userId)
            ->whereYear('fishing_logs.date', $currentYear)
            ->select(
                DB::raw('MONTH(fishing_logs.date) as month'),
                DB::raw('COUNT(DISTINCT fishing_logs.date) as days')
            )
            ->groupBy(DB::raw('MONTH(fishing_logs.date)'))
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [\Carbon\Carbon::create()->month($item->month)->format('M') => $item->days]);

        return [
            'yoyComparison' => $yoyComparison,
            'personalBests' => $personalBests,
            'improvementRate' => $improvementRate,
            'fishingFrequency' => $fishingFrequency,
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
        $avgSize = (clone $baseQuery)->whereNotNull('fishing_logs.max_size')->avg('fishing_logs.max_size') ?? 0;
        $speciesCount = (clone $baseQuery)->whereNotNull('fishing_logs.user_fish_id')->distinct()->count('fishing_logs.user_fish_id');
        $locationCount = (clone $baseQuery)->whereNotNull('fishing_logs.user_location_id')->distinct()->count('fishing_logs.user_location_id');

        $fishingScore = round(
            ($totalCaught * 10) +
            ($avgSize * 5) +
            ($speciesCount * 50) +
            ($locationCount * 25)
        );

        // Achievement badges
        $badges = [];

        // Century Club (100+ fish)
        if ($totalCaught >= 100) {
            $badges[] = ['name' => 'Century Club', 'icon' => '💯', 'description' => '100+ fish caught'];
        }

        // Early Bird (catches before 7am)
        $earlyBirdCatches = (clone $baseQuery)
            ->whereNotNull('fishing_logs.time')
            ->whereRaw('HOUR(fishing_logs.time) < 7')
            ->where('fishing_logs.quantity', '>', 0)
            ->count();
        if ($earlyBirdCatches >= 10) {
            $badges[] = ['name' => 'Early Bird', 'icon' => '🌅', 'description' => '10+ catches before 7am'];
        }

        // Night Owl (catches after 8pm)
        $nightOwlCatches = (clone $baseQuery)
            ->whereNotNull('fishing_logs.time')
            ->whereRaw('HOUR(fishing_logs.time) >= 20')
            ->where('fishing_logs.quantity', '>', 0)
            ->count();
        if ($nightOwlCatches >= 10) {
            $badges[] = ['name' => 'Night Owl', 'icon' => '🦉', 'description' => '10+ catches after 8pm'];
        }

        // Explorer (10+ locations)
        if ($locationCount >= 10) {
            $badges[] = ['name' => 'Explorer', 'icon' => '🗺️', 'description' => '10+ locations fished'];
        }

        // Species Hunter (10+ species)
        if ($speciesCount >= 10) {
            $badges[] = ['name' => 'Species Hunter', 'icon' => '🎯', 'description' => '10+ species caught'];
        }

        // Trophy Hunter (fish 20"+)
        $trophyCatches = (clone $baseQuery)
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>=', 20)
            ->count();
        if ($trophyCatches >= 5) {
            $badges[] = ['name' => 'Trophy Hunter', 'icon' => '🏆', 'description' => '5+ fish over 20"'];
        }

        // Hot streak (current streak of successful days)
        $recentDays = FishingLog::where('fishing_logs.user_id', $userId)
            ->select('fishing_logs.date', DB::raw('MAX(fishing_logs.quantity) as max_qty'))
            ->groupBy('fishing_logs.date')
            ->orderByDesc('fishing_logs.date')
            ->limit(30)
            ->get();

        $hotStreak = 0;
        foreach ($recentDays as $day) {
            if ($day->max_qty > 0) {
                $hotStreak++;
            } else {
                break;
            }
        }

        // Lucky number (most common catch quantity)
        $luckyNumber = (clone $baseQuery)
            ->where('fishing_logs.quantity', '>', 0)
            ->select('fishing_logs.quantity', DB::raw('COUNT(*) as occurrences'))
            ->groupBy('fishing_logs.quantity')
            ->orderByDesc('occurrences')
            ->first();

        return [
            'fishingScore' => $fishingScore,
            'badges' => $badges,
            'hotStreak' => $hotStreak > 0 ? $hotStreak : null,
            'luckyNumber' => $luckyNumber ? [
                'number' => $luckyNumber->quantity,
                'occurrences' => $luckyNumber->occurrences,
            ] : null,
        ];
    }
}

