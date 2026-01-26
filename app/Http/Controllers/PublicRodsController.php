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
    public function index(User $user): Response
    {
        // Check if the authenticated user is following this user
        if (!auth()->user()->isFollowing($user)) {
            abort(403, 'You must be following this user to view their rods.');
        }

        $userId = $user->id;

        // Get all rods for this user with usage statistics
        $rods = Rod::where('user_id', $userId)
            ->withCount(['fishingLogs as total_trips' => function ($query) {
                $query->select(DB::raw('COUNT(DISTINCT date)'));
            }])
            ->withCount(['fishingLogs as total_catches' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->with(['fishingLogs' => function ($query) {
                $query->select('equipment_id', DB::raw('MAX(max_size) as biggest_fish'))
                    ->whereNotNull('max_size')
                    ->where('max_size', '>', 0)
                    ->groupBy('equipment_id');
            }])
            ->get()
            ->map(function ($rod) {
                // Get the biggest fish caught with this rod
                $biggestFish = FishingLog::where('equipment_id', $rod->id)
                    ->whereNotNull('max_size')
                    ->where('max_size', '>', 0)
                    ->orderByDesc('max_size')
                    ->first();

                // Get most caught species with this rod
                $topSpecies = FishingLog::where('equipment_id', $rod->id)
                    ->join('user_fish', 'fishing_logs.fish_id', '=', 'user_fish.id')
                    ->select('user_fish.species', DB::raw('SUM(fishing_logs.quantity) as total'))
                    ->whereNotNull('fishing_logs.fish_id')
                    ->groupBy('user_fish.species')
                    ->orderByDesc('total')
                    ->first();

                return [
                    'id' => $rod->id,
                    'rod_name' => $rod->rod_name,
                    'rod_weight' => $rod->rod_weight,
                    'rod_length' => $rod->rod_length,
                    'reel' => $rod->reel,
                    'line' => $rod->line,
                    'total_trips' => $rod->total_trips ?? 0,
                    'total_catches' => $rod->total_catches ?? 0,
                    'biggest_fish' => $biggestFish ? [
                        'size' => $biggestFish->max_size,
                        'species' => $biggestFish->fish?->species,
                        'date' => $biggestFish->date->format('M d, Y'),
                    ] : null,
                    'top_species' => $topSpecies ? [
                        'species' => $topSpecies->species,
                        'total' => $topSpecies->total,
                    ] : null,
                ];
            })
            ->sortByDesc('total_catches')
            ->values();

        return Inertia::render('PublicRods', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y'),
            ],
            'rods' => $rods,
        ]);
    }
}

