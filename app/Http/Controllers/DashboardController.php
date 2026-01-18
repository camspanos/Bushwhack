<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Fish;
use App\Models\FishingLog;
use App\Models\Fly;
use App\Models\Friend;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $userId = auth()->id();

        // Total counts
        $totalCatches = FishingLog::where('user_id', $userId)->sum('quantity') ?? 0;
        $totalTrips = FishingLog::where('user_id', $userId)->count();
        $totalLocations = Location::where('user_id', $userId)->count();
        $totalFriends = Friend::where('user_id', $userId)->count();

        // Favorite location (most visited)
        $favoriteLocation = FishingLog::where('user_id', $userId)
            ->select('location_id', DB::raw('COUNT(*) as visit_count'))
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->orderByDesc('visit_count')
            ->with('location')
            ->first();

        // Most caught fish species
        $topFish = FishingLog::where('user_id', $userId)
            ->select('fish_id', DB::raw('SUM(quantity) as total_caught'))
            ->whereNotNull('fish_id')
            ->groupBy('fish_id')
            ->orderByDesc('total_caught')
            ->with('fish')
            ->first();

        // Biggest catch
        $biggestCatch = FishingLog::where('user_id', $userId)
            ->whereNotNull('max_size')
            ->orderByDesc('max_size')
            ->with(['fish', 'location'])
            ->first();

        // Recent fishing logs
        $recentLogs = FishingLog::where('user_id', $userId)
            ->with(['location', 'fish', 'fly', 'equipment', 'friends'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        // Catches by month (last 6 months)
        $catchesByMonth = FishingLog::where('user_id', $userId)
            ->where('date', '>=', now()->subMonths(6))
            ->select(
                DB::raw("strftime('%Y-%m', date) as month"),
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

        // Top 5 locations by catches
        $topLocations = FishingLog::where('user_id', $userId)
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
            'recentLogs' => $recentLogs,
            'catchesByMonth' => $catchesByMonth,
            'topLocations' => $topLocations,
        ]);
    }
}
