<?php

namespace App\Http\Controllers;

use App\Models\UserFish;
use App\Models\FishingLog;
use App\Models\UserFly;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicDashboardController extends Controller
{
    public function show(User $user, Request $request): Response
    {
        // Check if the authenticated user is following this user
        if (!auth()->user()->isFollowing($user)) {
            abort(403, 'You must be following this user to view their dashboard.');
        }

        $userId = $user->id;
        $authUser = auth()->user();

        // Create cache key based on viewed user and year filter
        $yearFilter = $request->input('year', (string) now()->year);
        $cacheKey = "public_dashboard_{$userId}_{$yearFilter}";

        // Cache dashboard data for 1 hour
        $data = Cache::remember($cacheKey, 3600, function () use ($user, $userId, $authUser, $request) {
            return $this->getPublicDashboardData($user, $userId, $authUser, $request);
        });

        return Inertia::render('PublicDashboard', $data);
    }

    private function getPublicDashboardData(User $user, $userId, $authUser, Request $request): array
    {

        // Free users can only view current year data on public dashboards, UNLESS viewing user_id 1
        if (!$authUser->canFilterByYear() && $userId !== 1) {
            $yearFilter = (string) now()->year;
            $availableYears = [$yearFilter];
        } else {
            // Get available years from fishing logs
            $availableYears = FishingLog::where('user_id', $userId)
                ->selectRaw('DISTINCT YEAR(date) as year')
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->filter()
                ->values()
                ->toArray();

            // Get the year filter from request, default to current year if it has data, otherwise lifetime
            $currentYear = now()->year;
            $hasCurrentYearData = in_array($currentYear, $availableYears);
            $defaultYear = $hasCurrentYearData ? (string) $currentYear : 'lifetime';
            $yearFilter = $request->input('year', $defaultYear);
        }

        // Build base query with year filter
        $baseQuery = FishingLog::where('user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $baseQuery->whereYear('date', $yearFilter);
        }

        // Total counts (filtered by year)
        $totalCatches = (clone $baseQuery)->sum('quantity') ?? 0;
        $totalTrips = (clone $baseQuery)->distinct()->count('date');

        // Most caught fish species (filtered by year)
        $topFish = (clone $baseQuery)
            ->select('user_fish_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('user_fish_id')
            ->groupBy('user_fish_id')
            ->orderByDesc('total_caught')
            ->with('fish')
            ->first();

        // Biggest catch (filtered by year)
        $biggestCatch = (clone $baseQuery)
            ->whereNotNull('max_size')
            ->orderByDesc('max_size')
            ->with(['fish', 'location', 'fly', 'rod'])
            ->first();

        // All species caught with statistics (filtered by year)
        $allSpecies = (clone $baseQuery)
            ->select('user_fish_id', DB::raw('SUM(quantity) as total_caught'), DB::raw('MAX(max_size) as biggest_size'), DB::raw('COUNT(DISTINCT date) as trip_count'))
            ->whereNotNull('user_fish_id')
            ->groupBy('user_fish_id')
            ->orderByDesc('total_caught')
            ->with('fish')
            ->get()
            ->map(function ($item) {
                return [
                    'species' => $item->fish?->species ?? 'Unknown',
                    'water_type' => $item->fish?->water_type ?? null,
                    'total_caught' => $item->total_caught ?? 0,
                    'biggest_size' => $item->biggest_size ?? 0,
                    'trip_count' => $item->trip_count ?? 0,
                ];
            });

        // Catches by month (filtered by year or last 6 months for lifetime)
        $monthQuery = FishingLog::where('user_id', $userId);
        if ($yearFilter === 'lifetime') {
            $monthQuery->where('date', '>=', now()->subMonths(6));
        } else {
            $monthQuery->whereYear('date', $yearFilter);
        }
        $catchesByMonth = $monthQuery
            ->select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                DB::raw('SUM(quantity) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => date('M Y', strtotime($item->month . '-01')),
                    'total' => $item->total ?? 0,
                ];
            });



        // Filtered fishing logs
        $filteredLogs = (clone $baseQuery)->get();

        // Days fished (unique dates, filtered by year)
        $daysFished = $filteredLogs->pluck('date')->unique()->count();

        // Days with fish caught
        $daysWithFish = $filteredLogs
            ->filter(fn($log) => $log->quantity > 0)
            ->pluck('date')
            ->unique()
            ->count();

        // Days skunked (no fish caught)
        $daysSkunked = $filteredLogs
            ->filter(fn($log) => !$log->quantity || $log->quantity === 0)
            ->pluck('date')
            ->unique()
            ->count();

        // Most caught in a day (sum all catches per day, then find max)
        $mostInDay = $filteredLogs
            ->groupBy('date')
            ->map(fn($logs) => $logs->sum('quantity'))
            ->max() ?? 0;

        // Success rate
        $successRate = $daysFished > 0 ? round(($daysWithFish / $daysFished) * 100, 1) : 0;

        // Favorite weekday - most fished day of the week (filtered by year)
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

        $favoriteWeekday = $weekdayData ? [
            'day' => $weekdayData->weekday,
            'count' => $weekdayData->trip_count,
        ] : null;



        // Most successful fly (filtered by year, grouped by fly name)
        $mostSuccessfulFlyQuery = FishingLog::where('fishing_logs.user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $mostSuccessfulFlyQuery->whereYear('fishing_logs.date', $yearFilter);
        }
        $mostSuccessfulFly = $mostSuccessfulFlyQuery
            ->join('user_flies', 'fishing_logs.user_fly_id', '=', 'user_flies.id')
            ->select('user_flies.name', DB::raw('SUM(fishing_logs.quantity) as total_caught'), DB::raw('COUNT(DISTINCT fishing_logs.date) as days_used'))
            ->whereNotNull('fishing_logs.user_fly_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->where('user_flies.user_id', $userId)
            ->groupBy('user_flies.name')
            ->orderByDesc('total_caught')
            ->first();

        // Fly with biggest fish (filtered by year, grouped by fly name)
        $biggestFishFlyQuery = FishingLog::where('fishing_logs.user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $biggestFishFlyQuery->whereYear('fishing_logs.date', $yearFilter);
        }
        $biggestFishFly = $biggestFishFlyQuery
            ->join('user_flies', 'fishing_logs.user_fly_id', '=', 'user_flies.id')
            ->select('user_flies.name', DB::raw('MAX(fishing_logs.max_size) as biggest_size'), DB::raw('COUNT(DISTINCT fishing_logs.date) as days_used'))
            ->whereNotNull('fishing_logs.user_fly_id')
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->where('user_flies.user_id', $userId)
            ->groupBy('user_flies.name')
            ->orderByDesc('biggest_size')
            ->first();

        // Catches over time - for line chart (filtered by year)
        $catchesOverTimeQuery = (clone $baseQuery);

        if ($yearFilter === 'lifetime') {
            // For lifetime, show last 12 months grouped by month
            $catchesOverTimeQuery->where('date', '>=', now()->subMonths(12));
            $groupBy = "DATE_FORMAT(date, '%Y-%m')";
            $dateFormat = '%Y-%m-01'; // First day of month for consistent formatting
        } else {
            // For specific year, group by week to keep it manageable
            $groupBy = "DATE_FORMAT(date, '%Y-%u')";
            $dateFormat = '%Y-%m-%d'; // Use actual date
        }

        $catchesOverTime = $catchesOverTimeQuery
            ->select(
                DB::raw("$groupBy as period"),
                DB::raw("MIN(date) as date"), // Get first date in the period for display
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

        // Streak tracker - calculate streaks of consecutive calendar days with catches (filtered by year)
        $streakQuery = FishingLog::where('user_id', $userId)
            ->where('quantity', '>', 0);
        if ($yearFilter !== 'lifetime') {
            $streakQuery->whereYear('date', $yearFilter);
        }
        $allTripsOrdered = $streakQuery
            ->orderBy('date')
            ->get()
            ->groupBy(function($log) {
                return \Carbon\Carbon::parse($log->date)->format('Y-m-d');
            });

        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 0;
        $lastDate = null;

        foreach ($allTripsOrdered as $date => $logs) {
            $currentDate = \Carbon\Carbon::parse($date);

            // Check if this is consecutive to the last successful day
            if ($lastDate === null) {
                $tempStreak = 1;
            } elseif ($lastDate->copy()->addDay()->isSameDay($currentDate)) {
                // Current date is exactly 1 day after last date
                $tempStreak++;
            } else {
                // Not consecutive, save the streak and start a new one
                $longestStreak = max($longestStreak, $tempStreak);
                $tempStreak = 1;
            }
            $lastDate = $currentDate;
        }
        $longestStreak = max($longestStreak, $tempStreak);

        // Calculate current streak (only counts if they fished today or yesterday, filtered by year)
        $today = \Carbon\Carbon::today();
        $recentTripsQuery = FishingLog::where('user_id', $userId)
            ->where('quantity', '>', 0);
        if ($yearFilter !== 'lifetime') {
            $recentTripsQuery->whereYear('date', $yearFilter);
        }
        $recentTrips = $recentTripsQuery
            ->orderByDesc('date')
            ->get()
            ->groupBy(function($log) {
                return \Carbon\Carbon::parse($log->date)->format('Y-m-d');
            });

        $expectedDate = $today;
        foreach ($recentTrips as $date => $logs) {
            $tripDate = \Carbon\Carbon::parse($date);

            if ($tripDate->isSameDay($expectedDate)) {
                $currentStreak++;
                $expectedDate = $expectedDate->subDay();
            } else {
                break;
            }
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'stats' => [
                'totalCatches' => $totalCatches,
                'totalTrips' => $totalTrips,
                'topFish' => $topFish?->fish?->species,
                'topFishCount' => $topFish?->total_caught ?? 0,
                'biggestCatch' => $biggestCatch ? [
                    'size' => $biggestCatch->max_size,
                    'species' => $biggestCatch->fish?->species,
                    'date' => $biggestCatch->date,
                    'fly' => $biggestCatch->fly?->name,
                    'rod' => $biggestCatch->rod?->rod_name,
                ] : null,
            ],
            'allSpecies' => $allSpecies,
            'catchesByMonth' => $catchesByMonth,
            'mostSuccessfulFly' => $mostSuccessfulFly ? [
                'name' => $mostSuccessfulFly->name,
                'total' => $mostSuccessfulFly->total_caught ?? 0,
                'days' => $mostSuccessfulFly->days_used ?? 0,
            ] : null,
            'biggestFishFly' => $biggestFishFly ? [
                'name' => $biggestFishFly->name,
                'size' => $biggestFishFly->biggest_size ?? 0,
                'days' => $biggestFishFly->days_used ?? 0,
            ] : null,
            'yearStats' => [
                'daysFished' => $daysFished,
                'daysWithFish' => $daysWithFish,
                'daysSkunked' => $daysSkunked,
                'mostInDay' => $mostInDay,
                'successRate' => $successRate,
            ],
            'favoriteWeekday' => $favoriteWeekday,
            'catchesOverTime' => $catchesOverTime,
            'streakStats' => [
                'currentStreak' => $currentStreak,
                'longestStreak' => $longestStreak,
            ],
            'availableYears' => $availableYears,
            'selectedYear' => $yearFilter,
        ];
    }
}

