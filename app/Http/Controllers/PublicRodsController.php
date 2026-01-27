<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRod;
use App\Models\FishingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class PublicRodsController extends Controller
{
    public function index(User $user, Request $request): Response
    {
        // Check if the authenticated user is following this user
        if (!auth()->user()->isFollowing($user)) {
            abort(403, 'You must be following this user to view their rods.');
        }

        $userId = $user->id;
        $authUser = auth()->user();

        // Determine year filter
        $yearFilter = $this->getYearFilter($userId, $authUser, $request);

        // Create cache key
        $cacheKey = "public_rods_{$userId}_{$yearFilter}";

        // Cache data for 1 hour
        $data = Cache::remember($cacheKey, 3600, function () use ($user, $userId, $authUser, $request) {
            return $this->getPublicRodsData($user, $userId, $authUser, $request);
        });

        return Inertia::render('PublicRods', $data);
    }

    private function getYearFilter($userId, $authUser, Request $request): string
    {
        // Free users can only view current year data on public pages, UNLESS viewing user_id 1
        if (!$authUser->canFilterByYear() && $userId !== 1) {
            return (string) now()->year;
        }

        // Get available years from fishing logs
        $availableYears = FishingLog::where('user_id', $userId)
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($year) => (string) $year)
            ->toArray();

        // Get the year filter from request, default to current year if it has data, otherwise lifetime
        $currentYear = now()->year;
        $hasCurrentYearData = in_array((string) $currentYear, $availableYears);
        $defaultYear = $hasCurrentYearData ? (string) $currentYear : 'lifetime';

        return $request->input('year', $defaultYear);
    }

    private function getPublicRodsData(User $user, $userId, $authUser, Request $request): array
    {
        // Free users can only view current year data on public pages, UNLESS viewing user_id 1
        if (!$authUser->canFilterByYear() && $userId !== 1) {
            $yearFilter = (string) now()->year;
            $availableYears = [$yearFilter];
        } else {
            // Get available years from fishing logs
            $availableYears = FishingLog::where('user_id', $userId)
                ->selectRaw('DISTINCT YEAR(date) as year')
                ->orderByDesc('year')
                ->pluck('year')
                ->map(fn($year) => (string) $year)
                ->toArray();

            // Get the year filter from request, default to current year if it has data, otherwise lifetime
            $currentYear = now()->year;
            $hasCurrentYearData = in_array((string) $currentYear, $availableYears);
            $defaultYear = $hasCurrentYearData ? (string) $currentYear : 'lifetime';
            $yearFilter = $request->input('year', $defaultYear);
        }

        // Get all rods for this user with usage statistics
        // Using the same query structure as RodController::statistics()
        $rods = UserRod::where('user_id', $userId)
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'user_rod_id', 'quantity', 'max_size', 'date');
                if ($yearFilter !== 'lifetime') {
                    $query->whereYear('date', $yearFilter);
                }
            }])
            ->get()
            ->map(function ($item) {
                $logs = $item->fishingLogs;
                $totalTrips = $logs->count();
                $totalFish = $logs->sum('quantity');
                $biggestFish = $logs->max('max_size') ?? 0;
                $successfulTrips = $logs->where('quantity', '>', 0)->count();
                $successRate = $totalTrips > 0
                    ? round(($successfulTrips / $totalTrips) * 100, 1)
                    : 0;

                return [
                    'id' => $item->id,
                    'rod_name' => $item->rod_name,
                    'rod_weight' => $item->rod_weight,
                    'rod_length' => $item->rod_length,
                    'reel' => $item->reel,
                    'line' => $item->line,
                    'totalTrips' => $totalTrips,
                    'totalFish' => $totalFish,
                    'biggestFish' => (float) $biggestFish,
                    'successRate' => $successRate,
                ];
            })
            ->filter(function ($rod) {
                // Only include rods that have trips in the filtered date range
                return $rod['totalTrips'] > 0;
            })
            ->sortByDesc('totalFish')
            ->values();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'rods' => $rods,
            'availableYears' => $availableYears,
            'selectedYear' => $yearFilter,
        ];
    }
}

