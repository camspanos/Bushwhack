<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use App\Models\FishingLog;
use App\Models\Fly;
use App\Models\Friend;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Get available years from fishing logs (show all years to everyone)
        $availableYears = FishingLog::where('user_id', $userId)
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->map(fn($year) => (string) $year)
            ->values()
            ->toArray();

        // Always include current year even if no data exists for it
        $currentYear = (string) now()->year;
        if (!in_array($currentYear, $availableYears)) {
            array_unshift($availableYears, $currentYear);
        }

        // Free users can only view current year data (but can see all years in dropdown)
        if (!$user->canFilterByYear()) {
            $yearFilter = $currentYear;
        } else {
            // Get the year filter from request, default to current year
            $yearFilter = $request->input('year', $currentYear);
        }

        // Build base query with year filter
        $baseQuery = FishingLog::where('user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $baseQuery->whereYear('date', $yearFilter);
        }

        // Total counts (filtered by year)
        $totalCatches = (clone $baseQuery)->sum('quantity') ?? 0;
        $totalTrips = (clone $baseQuery)->distinct()->count('date');

        // Total locations (always lifetime)
        $totalLocations = Location::where('user_id', $userId)->count();

        // Total friends fished with (filtered by year)
        // Count distinct friends from fishing logs in the date range
        $totalFriends = DB::table('fishing_log_user_friend')
            ->join('fishing_logs', 'fishing_log_user_friend.fishing_log_id', '=', 'fishing_logs.id')
            ->where('fishing_logs.user_id', $userId);

        if ($yearFilter !== 'lifetime') {
            $totalFriends->whereYear('fishing_logs.date', $yearFilter);
        }

        $totalFriends = $totalFriends->distinct('fishing_log_user_friend.friend_id')->count('fishing_log_user_friend.friend_id');

        // Favorite location (most visited, filtered by year)
        $favoriteLocation = (clone $baseQuery)
            ->select('location_id', DB::raw('COUNT(DISTINCT date) as visit_count'))
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->orderByDesc('visit_count')
            ->with('location')
            ->first();

        // Most caught fish species (filtered by year)
        $topFish = (clone $baseQuery)
            ->select('fish_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('fish_id')
            ->groupBy('fish_id')
            ->orderByDesc('total_caught')
            ->with('fish')
            ->first();

        // Biggest catch (filtered by year)
        $biggestCatch = (clone $baseQuery)
            ->whereNotNull('max_size')
            ->orderByDesc('max_size')
            ->with(['fish', 'location'])
            ->first();

        // All species caught with statistics (filtered by year)
        $allSpecies = (clone $baseQuery)
            ->select('fish_id', DB::raw('SUM(quantity) as total_caught'), DB::raw('MAX(max_size) as biggest_size'), DB::raw('COUNT(DISTINCT date) as trip_count'))
            ->whereNotNull('fish_id')
            ->groupBy('fish_id')
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

        // Top 7 locations by catches (filtered by year)
        $topLocations = (clone $baseQuery)
            ->select('location_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->orderByDesc('total_caught')
            ->with('location')
            ->limit(7)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->location->name ?? 'Unknown',
                    'city' => $item->location->city ?? null,
                    'state' => $item->location->state ?? null,
                    'total' => $item->total_caught ?? 0,
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

        // Most productive location (filtered by year)
        $mostProductiveLocation = (clone $baseQuery)
            ->select('location_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('location_id')
            ->where('quantity', '>', 0)
            ->groupBy('location_id')
            ->orderByDesc('total_caught')
            ->with('location')
            ->first();

        // Most successful fly (filtered by year, grouped by fly name)
        $mostSuccessfulFlyQuery = FishingLog::where('fishing_logs.user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $mostSuccessfulFlyQuery->whereYear('fishing_logs.date', $yearFilter);
        }
        $mostSuccessfulFly = $mostSuccessfulFlyQuery
            ->join('user_flies', 'fishing_logs.fly_id', '=', 'user_flies.id')
            ->select('user_flies.name', DB::raw('SUM(fishing_logs.quantity) as total_caught'), DB::raw('COUNT(DISTINCT fishing_logs.date) as days_used'))
            ->whereNotNull('fishing_logs.fly_id')
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
            ->join('user_flies', 'fishing_logs.fly_id', '=', 'user_flies.id')
            ->select('user_flies.name', DB::raw('MAX(fishing_logs.max_size) as biggest_size'), DB::raw('COUNT(DISTINCT fishing_logs.date) as days_used'))
            ->whereNotNull('fishing_logs.fly_id')
            ->whereNotNull('fishing_logs.max_size')
            ->where('fishing_logs.max_size', '>', 0)
            ->where('user_flies.user_id', $userId)
            ->groupBy('user_flies.name')
            ->orderByDesc('biggest_size')
            ->first();

        // Catches over time - for line chart (filtered by year)
        // Aggregate by week or month depending on data volume
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

        // Calculate current streak (only counts if you fished today or yesterday, filtered by year)
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

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalCatches' => $totalCatches,
                'totalTrips' => $totalTrips,
                'totalLocations' => $totalLocations,
                'totalFriends' => $totalFriends,
                'favoriteLocation' => $favoriteLocation?->location?->name,
                'topFish' => $topFish?->fish?->species,
                'topFishCount' => $topFish?->total_caught ?? 0,
                'biggestCatch' => $biggestCatch ? [
                    'size' => $biggestCatch->max_size,
                    'species' => $biggestCatch->fish?->species,
                    'location' => $biggestCatch->location?->name,
                    'date' => $biggestCatch->date,
                ] : null,
            ],
            'allSpecies' => $allSpecies,
            'catchesByMonth' => $catchesByMonth,
            'topLocations' => $topLocations,
            'mostProductiveLocation' => $mostProductiveLocation ? [
                'name' => $mostProductiveLocation->location?->name,
                'total' => $mostProductiveLocation->total_caught ?? 0,
            ] : null,
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
        ]);
    }
}
