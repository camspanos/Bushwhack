<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFish;
use App\Models\FishingLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class PublicFishController extends Controller
{
    public function index(User $user, Request $request): Response
    {
        // Check if the authenticated user is following this user
        if (!auth()->user()->isFollowing($user)) {
            abort(403, 'You must be following this user to view their fish species.');
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

        // Get all fish species for this user with catch statistics
        // Using the same query structure as FishController::statistics()
        $fishSpecies = UserFish::where('user_id', $userId)
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'user_fish_id', 'quantity', 'max_size', 'date', 'user_location_id');
                if ($yearFilter !== 'lifetime') {
                    $query->whereYear('date', $yearFilter);
                }
            }])
            ->get()
            ->map(function ($fishSpecies) {
                $logs = $fishSpecies->fishingLogs;
                $totalCaught = $logs->sum('quantity');

                // Count unique trips by date and location
                $totalTrips = $logs->groupBy(function ($log) {
                    return $log->date->format('Y-m-d') . '-' . $log->location_id;
                })->count();

                $biggestFish = $logs->max('max_size') ?? 0;
                $avgSize = $totalCaught > 0
                    ? round($logs->sum('max_size') / $totalCaught, 1)
                    : 0;

                return [
                    'id' => $fishSpecies->id,
                    'species' => $fishSpecies->species,
                    'water_type' => $fishSpecies->water_type,
                    'totalCaught' => $totalCaught,
                    'totalTrips' => $totalTrips,
                    'biggestFish' => $biggestFish,
                    'avgSize' => $avgSize,
                ];
            })
            ->filter(function ($fishSpecies) {
                // Only include fish species that have been caught in the filtered date range
                return $fishSpecies['totalCaught'] > 0;
            })
            ->sortByDesc('totalCaught')
            ->values();

        return Inertia::render('PublicFish', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'fishSpecies' => $fishSpecies,
            'availableYears' => $availableYears,
            'selectedYear' => $yearFilter,
        ]);
    }
}

