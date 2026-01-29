<?php

namespace App\Http\Controllers;

use App\Models\UserFish;
use App\Models\FishingLog;
use App\Models\UserFly;
use App\Models\UserFriend;
use App\Models\UserLocation;
use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardDataService $dashboardService
    ) {}

    public function index(Request $request): Response
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Create cache key based on user and year filter
        $yearFilter = $request->input('year', (string) now()->year);
        $cacheKey = "dashboard_{$userId}_{$yearFilter}";

        // Cache dashboard data for 1 hour
        $data = Cache::remember($cacheKey, 3600, function () use ($userId, $user, $request) {
            return $this->getDashboardData($userId, $user, $request);
        });

        return Inertia::render('Dashboard', $data);
    }

    private function getDashboardData($userId, $user, Request $request): array
    {
        // Get available years from fishing logs (show all years to everyone)
        $availableYears = $this->dashboardService->getAvailableYears($userId);
        $currentYear = (string) now()->year;

        // Free users can only view current year data (but can see all years in dropdown)
        if (!$user->canFilterByYear()) {
            $yearFilter = $currentYear;
        } else {
            // Get the year filter from request, default to current year
            $yearFilter = $request->input('year', $currentYear);
        }

        // Build base query with year filter
        $baseQuery = $this->dashboardService->buildBaseQuery($userId, $yearFilter);

        // Total counts (filtered by year)
        $totalCatches = (clone $baseQuery)->sum('quantity') ?? 0;
        $totalTrips = (clone $baseQuery)->distinct()->count('date');

        // Total locations (always lifetime)
        $totalLocations = UserLocation::where('user_id', $userId)->count();

        // Total friends fished with (filtered by year)
        // Count distinct friends from fishing logs in the date range
        $totalFriends = DB::table('fishing_log_user_friend')
            ->join('fishing_logs', 'fishing_log_user_friend.fishing_log_id', '=', 'fishing_logs.id')
            ->where('fishing_logs.user_id', $userId);

        if ($yearFilter !== 'lifetime') {
            $totalFriends->whereYear('fishing_logs.date', $yearFilter);
        }

        $totalFriends = $totalFriends->distinct('fishing_log_user_friend.user_friend_id')->count('fishing_log_user_friend.user_friend_id');

        // Favorite location (most visited, filtered by year)
        $favoriteLocation = (clone $baseQuery)
            ->select('user_location_id', DB::raw('COUNT(DISTINCT date) as visit_count'))
            ->whereNotNull('user_location_id')
            ->groupBy('user_location_id')
            ->orderByDesc('visit_count')
            ->with('location')
            ->first();

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
            ->with(['fish', 'location', 'fly', 'rod', 'friends'])
            ->first();

        // Second biggest catch (filtered by year)
        $secondBiggestCatch = (clone $baseQuery)
            ->whereNotNull('max_size')
            ->orderByDesc('max_size')
            ->with(['fish', 'location', 'fly', 'rod', 'friends'])
            ->skip(1)
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

        // Top 7 species by biggest fish size (filtered by year)
        $topSpeciesBySize = (clone $baseQuery)
            ->select('user_fish_id', DB::raw('MAX(max_size) as biggest_size'), DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('user_fish_id')
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->groupBy('user_fish_id')
            ->orderByDesc('biggest_size')
            ->with('fish')
            ->limit(7)
            ->get()
            ->map(function ($item) {
                return [
                    'species' => $item->fish?->species ?? 'Unknown',
                    'water_type' => $item->fish?->water_type ?? null,
                    'biggest_size' => $item->biggest_size ?? 0,
                    'total_caught' => $item->total_caught ?? 0,
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
            ->select('user_location_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('user_location_id')
            ->groupBy('user_location_id')
            ->orderByDesc('total_caught')
            ->with('location.country')
            ->limit(7)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->location->name ?? 'Unknown',
                    'city' => $item->location->city ?? null,
                    'state' => $item->location->state ?? null,
                    'country' => $item->location->country->name ?? null,
                    'total' => $item->total_caught ?? 0,
                ];
            });

        // Top 7 locations by biggest fish size (filtered by year)
        $topLocationsBySize = (clone $baseQuery)
            ->select('user_location_id', DB::raw('MAX(max_size) as biggest_size'))
            ->whereNotNull('user_location_id')
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->groupBy('user_location_id')
            ->orderByDesc('biggest_size')
            ->with('location.country')
            ->limit(7)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->location->name ?? 'Unknown',
                    'city' => $item->location->city ?? null,
                    'state' => $item->location->state ?? null,
                    'country' => $item->location->country->name ?? null,
                    'biggest_size' => $item->biggest_size ?? 0,
                ];
            });

        // Year statistics using service
        $yearStats = $this->dashboardService->getYearStats(clone $baseQuery);

        // Favorite weekday using service
        $favoriteWeekday = $this->dashboardService->getFavoriteWeekday(clone $baseQuery);

        // Most productive location (filtered by year)
        $mostProductiveLocation = (clone $baseQuery)
            ->select('user_location_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('user_location_id')
            ->where('quantity', '>', 0)
            ->groupBy('user_location_id')
            ->orderByDesc('total_caught')
            ->with('location')
            ->first();

        // Fly statistics using service
        $flyStatsData = $this->dashboardService->getFlyStats($userId, $yearFilter);
        $mostSuccessfulFly = $flyStatsData['mostSuccessfulFly'];
        $biggestFishFly = $flyStatsData['biggestFishFly'];
        $mostSuccessfulFlyType = $flyStatsData['mostSuccessfulFlyType'];
        $mostSuccessfulFlyColor = $flyStatsData['mostSuccessfulFlyColor'];

        // Fish caught per month (for pie chart, filtered by year)
        $catchesByMonthPieQuery = FishingLog::where('user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $catchesByMonthPieQuery->whereYear('date', $yearFilter);
        }
        $catchesByMonthPie = $catchesByMonthPieQuery
            ->select(
                DB::raw("DATE_FORMAT(date, '%M') as month"),
                DB::raw("MONTH(date) as month_number"),
                DB::raw('SUM(quantity) as total_caught')
            )
            ->where('quantity', '>', 0)
            ->groupBy('month', 'month_number')
            ->orderBy('month_number')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'total_caught' => $item->total_caught ?? 0,
                ];
            });

        // Fish caught by moon phase (for pie chart, filtered by year)
        $catchesByMoonPhaseQuery = FishingLog::where('user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $catchesByMoonPhaseQuery->whereYear('date', $yearFilter);
        }
        $catchesByMoonPhase = $catchesByMoonPhaseQuery
            ->select(
                'moon_phase',
                DB::raw('SUM(quantity) as total_caught')
            )
            ->whereNotNull('moon_phase')
            ->where('quantity', '>', 0)
            ->groupBy('moon_phase')
            ->orderByDesc('total_caught')
            ->get()
            ->map(function ($item) {
                return [
                    'moon_phase' => $item->moon_phase,
                    'total_caught' => $item->total_caught ?? 0,
                ];
            });

        // Fish caught by sun phase (for pie chart, filtered by year)
        $catchesBySunPhaseQuery = FishingLog::where('user_id', $userId);
        if ($yearFilter !== 'lifetime') {
            $catchesBySunPhaseQuery->whereYear('date', $yearFilter);
        }
        $catchesBySunPhase = $catchesBySunPhaseQuery
            ->select(
                'time_of_day',
                DB::raw('SUM(quantity) as total_caught')
            )
            ->whereNotNull('time_of_day')
            ->where('quantity', '>', 0)
            ->groupBy('time_of_day')
            ->orderByRaw("FIELD(time_of_day, 'Pre-dawn', 'Morning', 'Midday', 'Afternoon', 'Evening', 'Night')")
            ->get()
            ->map(function ($item) {
                return [
                    'time_of_day' => $item->time_of_day,
                    'total_caught' => $item->total_caught ?? 0,
                ];
            });

        // Catches over time using service
        $catchesOverTime = $this->dashboardService->getCatchesOverTime(clone $baseQuery, $yearFilter);

        // Streak statistics using service
        $streakStats = $this->dashboardService->getStreakStats($userId, $yearFilter);

        return [
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
                    'style' => $biggestCatch->style,
                    'fly' => $biggestCatch->fly?->name,
                    'rod' => $biggestCatch->rod?->rod_name,
                    'friends' => $biggestCatch->friends->pluck('name')->toArray(),
                    'notes' => $biggestCatch->notes,
                ] : null,
                'secondBiggestCatch' => $secondBiggestCatch ? [
                    'size' => $secondBiggestCatch->max_size,
                    'species' => $secondBiggestCatch->fish?->species,
                    'location' => $secondBiggestCatch->location?->name,
                    'date' => $secondBiggestCatch->date,
                    'style' => $secondBiggestCatch->style,
                    'fly' => $secondBiggestCatch->fly?->name,
                    'rod' => $secondBiggestCatch->rod?->rod_name,
                    'friends' => $secondBiggestCatch->friends->pluck('name')->toArray(),
                    'notes' => $secondBiggestCatch->notes,
                ] : null,
            ],
            'allSpecies' => $allSpecies,
            'catchesByMonth' => $catchesByMonth,
            'catchesByMonthPie' => $catchesByMonthPie,
            'catchesByMoonPhase' => $catchesByMoonPhase,
            'catchesBySunPhase' => $catchesBySunPhase,
            'topLocations' => $topLocations,
            'topLocationsBySize' => $topLocationsBySize,
            'topSpeciesBySize' => $topSpeciesBySize,
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
            'mostSuccessfulFlyType' => $mostSuccessfulFlyType ? [
                'type' => $mostSuccessfulFlyType->type,
                'total' => $mostSuccessfulFlyType->total_caught ?? 0,
                'days' => $mostSuccessfulFlyType->days_used ?? 0,
            ] : null,
            'mostSuccessfulFlyColor' => $mostSuccessfulFlyColor ? [
                'color' => $mostSuccessfulFlyColor->color,
                'total' => $mostSuccessfulFlyColor->total_caught ?? 0,
                'days' => $mostSuccessfulFlyColor->days_used ?? 0,
            ] : null,
            'yearStats' => $yearStats,
            'favoriteWeekday' => $favoriteWeekday,
            'catchesOverTime' => $catchesOverTime,
            'streakStats' => $streakStats,
            'availableYears' => $availableYears,
            'selectedYear' => $yearFilter,
        ];
    }
}
