<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Fly;
use App\Models\FishingLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class PublicFliesController extends Controller
{
    public function index(User $user, Request $request): Response
    {
        // Check if the authenticated user is following this user
        if (!auth()->user()->isFollowing($user)) {
            abort(403, 'You must be following this user to view their flies.');
        }

        $userId = $user->id;
        $authUser = auth()->user();

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

        // Get all flies for this user with usage statistics
        // Using the same query structure as FlyController::statistics()
        $flies = Fly::where('user_id', $userId)
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'fly_id', 'quantity', 'max_size', 'date');
                if ($yearFilter !== 'lifetime') {
                    $query->whereYear('date', $yearFilter);
                }
            }])
            ->get()
            ->groupBy('name')
            ->map(function ($flyGroup) {
                // Combine stats from all flies with the same name
                $allLogs = $flyGroup->flatMap->fishingLogs;
                $totalCaught = $allLogs->sum('quantity');
                $totalTrips = $allLogs->count();
                $biggestFish = $allLogs->max('max_size') ?? 0;
                $successfulTrips = $allLogs->where('quantity', '>', 0)->count();
                $successRate = $totalTrips > 0
                    ? round(($successfulTrips / $totalTrips) * 100, 1)
                    : 0;

                // Find the most used color (favorite color)
                $colorStats = $flyGroup->map(function ($fly) {
                    return [
                        'color' => $fly->color,
                        'caught' => $fly->fishingLogs->sum('quantity'),
                    ];
                })->sortByDesc('caught');

                $favoriteColor = $colorStats->first()['color'] ?? null;

                // Get the first fly for basic info
                $firstFly = $flyGroup->first();

                return [
                    'id' => $firstFly->id,
                    'name' => $firstFly->name,
                    'type' => $firstFly->type,
                    'favoriteColor' => $favoriteColor,
                    'totalCaught' => $totalCaught,
                    'totalTrips' => $totalTrips,
                    'biggestFish' => (float) $biggestFish,
                    'successRate' => $successRate,
                ];
            })
            ->filter(function ($fly) {
                // Only include flies that have trips in the filtered date range
                return $fly['totalTrips'] > 0;
            })
            ->sortByDesc('totalCaught')
            ->values();

        return Inertia::render('PublicFlies', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'flies' => $flies,
            'availableYears' => $availableYears,
            'selectedYear' => $yearFilter,
        ]);
    }
}

