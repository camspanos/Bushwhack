<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Fish;
use App\Models\FishingLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class PublicFishController extends Controller
{
    public function index(User $user): Response
    {
        // Check if the authenticated user is following this user
        if (!auth()->user()->isFollowing($user)) {
            abort(403, 'You must be following this user to view their fish species.');
        }

        $userId = $user->id;

        // Get all fish species for this user with catch statistics
        $fishSpecies = Fish::where('user_id', $userId)
            ->get()
            ->map(function ($fish) use ($userId) {
                // Get total catches for this species
                $totalCatches = FishingLog::where('user_id', $userId)
                    ->where('fish_id', $fish->id)
                    ->sum('quantity') ?? 0;

                // Get total trips where this species was caught
                $totalTrips = FishingLog::where('user_id', $userId)
                    ->where('fish_id', $fish->id)
                    ->distinct('date')
                    ->count('date');

                // Get biggest catch of this species
                $biggestCatch = FishingLog::where('user_id', $userId)
                    ->where('fish_id', $fish->id)
                    ->whereNotNull('max_size')
                    ->where('max_size', '>', 0)
                    ->orderByDesc('max_size')
                    ->first();

                // Get most successful location for this species
                $topLocation = FishingLog::where('user_id', $userId)
                    ->where('fish_id', $fish->id)
                    ->join('locations', 'fishing_logs.location_id', '=', 'locations.id')
                    ->select('locations.name', 'locations.city', 'locations.state', DB::raw('SUM(fishing_logs.quantity) as total'))
                    ->whereNotNull('fishing_logs.location_id')
                    ->groupBy('locations.id', 'locations.name', 'locations.city', 'locations.state')
                    ->orderByDesc('total')
                    ->first();

                // Get most successful fly for this species
                $topFly = FishingLog::where('user_id', $userId)
                    ->where('fish_id', $fish->id)
                    ->join('user_flies', 'fishing_logs.fly_id', '=', 'user_flies.id')
                    ->select('user_flies.name', DB::raw('SUM(fishing_logs.quantity) as total'))
                    ->whereNotNull('fishing_logs.fly_id')
                    ->groupBy('user_flies.id', 'user_flies.name')
                    ->orderByDesc('total')
                    ->first();

                return [
                    'id' => $fish->id,
                    'species' => $fish->species,
                    'water_type' => $fish->water_type,
                    'total_catches' => $totalCatches,
                    'total_trips' => $totalTrips,
                    'biggest_catch' => $biggestCatch ? [
                        'size' => $biggestCatch->max_size,
                        'date' => $biggestCatch->date->format('M d, Y'),
                        'location' => $biggestCatch->location?->name,
                    ] : null,
                    'top_location' => $topLocation ? [
                        'name' => $topLocation->name,
                        'city' => $topLocation->city,
                        'state' => $topLocation->state,
                        'total' => $topLocation->total,
                    ] : null,
                    'top_fly' => $topFly ? [
                        'name' => $topFly->name,
                        'total' => $topFly->total,
                    ] : null,
                ];
            })
            ->filter(fn($fish) => $fish['total_catches'] > 0)
            ->sortByDesc('total_catches')
            ->values();

        return Inertia::render('PublicFish', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'fishSpecies' => $fishSpecies,
        ]);
    }
}

