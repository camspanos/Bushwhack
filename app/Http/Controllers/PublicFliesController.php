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
    public function index(User $user): Response
    {
        // Check if the authenticated user is following this user
        if (!auth()->user()->isFollowing($user)) {
            abort(403, 'You must be following this user to view their flies.');
        }

        $userId = $user->id;

        // Get all flies for this user with usage statistics
        $flies = Fly::where('user_id', $userId)
            ->get()
            ->map(function ($fly) use ($userId) {
                // Get total catches with this fly
                $totalCatches = FishingLog::where('user_id', $userId)
                    ->where('fly_id', $fly->id)
                    ->sum('quantity') ?? 0;

                // Get total trips where this fly was used
                $totalTrips = FishingLog::where('user_id', $userId)
                    ->where('fly_id', $fly->id)
                    ->distinct('date')
                    ->count('date');

                // Get biggest fish caught with this fly
                $biggestFish = FishingLog::where('user_id', $userId)
                    ->where('fly_id', $fly->id)
                    ->whereNotNull('max_size')
                    ->where('max_size', '>', 0)
                    ->orderByDesc('max_size')
                    ->first();

                // Get most caught species with this fly
                $topSpecies = FishingLog::where('user_id', $userId)
                    ->where('fly_id', $fly->id)
                    ->join('user_fish', 'fishing_logs.fish_id', '=', 'user_fish.id')
                    ->select('user_fish.species', DB::raw('SUM(fishing_logs.quantity) as total'))
                    ->whereNotNull('fishing_logs.fish_id')
                    ->groupBy('user_fish.id', 'user_fish.species')
                    ->orderByDesc('total')
                    ->first();

                // Get most successful location with this fly
                $topLocation = FishingLog::where('user_id', $userId)
                    ->where('fly_id', $fly->id)
                    ->join('locations', 'fishing_logs.location_id', '=', 'locations.id')
                    ->select('locations.name', DB::raw('SUM(fishing_logs.quantity) as total'))
                    ->whereNotNull('fishing_logs.location_id')
                    ->groupBy('locations.id', 'locations.name')
                    ->orderByDesc('total')
                    ->first();

                return [
                    'id' => $fly->id,
                    'name' => $fly->name,
                    'color' => $fly->color,
                    'size' => $fly->size,
                    'type' => $fly->type,
                    'total_catches' => $totalCatches,
                    'total_trips' => $totalTrips,
                    'biggest_fish' => $biggestFish ? [
                        'size' => $biggestFish->max_size,
                        'species' => $biggestFish->fish?->species,
                        'date' => $biggestFish->date->format('M d, Y'),
                    ] : null,
                    'top_species' => $topSpecies ? [
                        'species' => $topSpecies->species,
                        'total' => $topSpecies->total,
                    ] : null,
                    'top_location' => $topLocation ? [
                        'name' => $topLocation->name,
                        'total' => $topLocation->total,
                    ] : null,
                ];
            })
            ->filter(fn($fly) => $fly['total_catches'] > 0)
            ->sortByDesc('total_catches')
            ->values();

        return Inertia::render('PublicFlies', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'flies' => $flies,
        ]);
    }
}

