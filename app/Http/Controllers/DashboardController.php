<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
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

        // Get the year filter from request, default to current year
        $currentYear = date('Y');
        $yearFilter = $request->input('year', $currentYear);

        // Get available years from fishing logs
        $availableYears = FishingLog::where('user_id', $userId)
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();

        // Build base query with year filter
        $baseQuery = FishingLog::where('user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $baseQuery->whereYear('date', $yearFilter);
        }

        // Total counts (filtered by year)
        $totalCatches = (clone $baseQuery)->sum('quantity') ?? 0;
        $totalTrips = (clone $baseQuery)->distinct()->count('date');

        // Total locations and friends (always lifetime)
        $totalLocations = Location::where('user_id', $userId)->count();
        $totalFriends = Friend::where('user_id', $userId)->count();

        // Favorite location (most visited, filtered by year)
        $favoriteLocation = (clone $baseQuery)
            ->select('location_id', DB::raw('COUNT(*) as visit_count'))
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

        // Top 5 locations by catches (filtered by year)
        $topLocations = (clone $baseQuery)
            ->select('location_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->orderByDesc('total_caught')
            ->with('location')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->location->name ?? 'Unknown',
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
            ->join('flies', 'fishing_logs.fly_id', '=', 'flies.id')
            ->select('flies.name', DB::raw('SUM(fishing_logs.quantity) as total_caught'), DB::raw('COUNT(DISTINCT fishing_logs.date) as days_used'))
            ->whereNotNull('fishing_logs.fly_id')
            ->where('fishing_logs.quantity', '>', 0)
            ->where('flies.user_id', $userId)
            ->groupBy('flies.name')
            ->orderByDesc('total_caught')
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

        // Streak tracker - calculate streaks of consecutive calendar days with catches
        $allTripsOrdered = FishingLog::where('user_id', $userId)
            ->where('quantity', '>', 0)
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

        // Calculate current streak (only counts if you fished today or yesterday)
        $today = \Carbon\Carbon::today();
        $recentTrips = FishingLog::where('user_id', $userId)
            ->where('quantity', '>', 0)
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
