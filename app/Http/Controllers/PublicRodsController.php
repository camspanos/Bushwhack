<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rod;
use App\Models\FishingLog;
use Illuminate\Http\Request;
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
        $yearFilter = $request->input('year', 'lifetime');

        // Get available years from fishing logs
        $availableYears = FishingLog::where('user_id', $userId)
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($year) => (string) $year)
            ->toArray();

        // Get all rods for this user with usage statistics
        // Using the same query structure as RodController::statistics()
        $rods = Rod::where('user_id', $userId)
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'equipment_id', 'quantity', 'max_size', 'date');
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

        return Inertia::render('PublicRods', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'rods' => $rods,
            'availableYears' => $availableYears,
            'selectedYear' => $yearFilter,
        ]);
    }
}

