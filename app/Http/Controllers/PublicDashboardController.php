<?php

namespace App\Http\Controllers;

use App\Models\UserFish;
use App\Models\FishingLog;
use App\Models\UserFly;
use App\Models\User;
use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicDashboardController extends Controller
{
    public function __construct(
        private DashboardDataService $dashboardService
    ) {}

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
            // Get available years from fishing logs using service
            $availableYears = $this->dashboardService->getAvailableYears($userId);

            // Get the year filter from request, default to current year if it has data, otherwise lifetime
            $currentYear = now()->year;
            $hasCurrentYearData = in_array((string) $currentYear, $availableYears);
            $defaultYear = $hasCurrentYearData ? (string) $currentYear : 'lifetime';
            $yearFilter = $request->input('year', $defaultYear);
        }

        // Build base query with year filter using service
        $baseQuery = $this->dashboardService->buildBaseQuery($userId, $yearFilter);

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
        // Year statistics using service
        $yearStats = $this->dashboardService->getYearStats(clone $baseQuery);

        // Favorite weekday using service
        $favoriteWeekday = $this->dashboardService->getFavoriteWeekday(clone $baseQuery);

        // Fly statistics using service
        $flyStatsData = $this->dashboardService->getFlyStats($userId, $yearFilter);
        $mostSuccessfulFly = $flyStatsData['mostSuccessfulFly'];
        $biggestFishFly = $flyStatsData['biggestFishFly'];

        // Catches over time using service
        $catchesOverTime = $this->dashboardService->getCatchesOverTime(clone $baseQuery, $yearFilter);

        // Streak statistics using service
        $streakStats = $this->dashboardService->getStreakStats($userId, $yearFilter);

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
            'yearStats' => $yearStats,
            'favoriteWeekday' => $favoriteWeekday,
            'catchesOverTime' => $catchesOverTime,
            'streakStats' => $streakStats,
            'availableYears' => $availableYears,
            'selectedYear' => $yearFilter,
        ];
    }
}

