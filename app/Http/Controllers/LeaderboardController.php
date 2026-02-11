<?php

namespace App\Http\Controllers;

use App\Models\FishSpecies;
use App\Models\FishingLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboard.
     */
    public function index(Request $request): Response
    {
        $month = $request->input('month', now()->format('Y-m'));
        $waterType = $request->input('water_type', 'all');

        // Create a cache key based on the filters
        $cacheKey = "leaderboard_{$month}_{$waterType}";

        // Cache the leaderboard data for 1 hour
        $leaderboard = Cache::remember($cacheKey, 3600, function () use ($month, $waterType) {
            // Parse the month input - handle both Y-m format and year-only format
            if (strlen($month) === 4 && is_numeric($month)) {
                // Year only - get full year range
                $startDate = Carbon::parse($month . '-01-01')->startOfYear();
                $endDate = Carbon::parse($month . '-12-31')->endOfYear();
            } else {
                // Month format (Y-m)
                $date = Carbon::parse($month . '-01');
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
            }

            // OPTIMIZED: Get all biggest fish (by size) in one query using window functions
            $biggestFishQuery = FishingLog::join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
                ->join('users', 'fishing_logs.user_id', '=', 'users.id')
                ->join('fish_species', 'user_fish.fish_species_id', '=', 'fish_species.id')
                ->where('users.is_premium', true)
                ->whereBetween('fishing_logs.date', [$startDate, $endDate])
                ->whereNotNull('fishing_logs.max_size');

            if ($waterType !== 'all') {
                $biggestFishQuery->where('fish_species.water_type', $waterType);
            }

            $biggestFish = $biggestFishQuery
                ->selectRaw('
                    fish_species.id as species_id,
                    fish_species.species,
                    fish_species.water_type,
                    fishing_logs.max_size,
                    fishing_logs.date,
                    users.id as user_id,
                    users.name as user_name,
                    ROW_NUMBER() OVER (PARTITION BY fish_species.id ORDER BY fishing_logs.max_size DESC) as rn
                ')
                ->get()
                ->where('rn', 1)
                ->keyBy('species_id');

            // OPTIMIZED: Get all heaviest fish (by weight) in one query using window functions
            $heaviestFishQuery = FishingLog::join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
                ->join('users', 'fishing_logs.user_id', '=', 'users.id')
                ->join('fish_species', 'user_fish.fish_species_id', '=', 'fish_species.id')
                ->where('users.is_premium', true)
                ->whereBetween('fishing_logs.date', [$startDate, $endDate])
                ->whereNotNull('fishing_logs.max_weight')
                ->where('fishing_logs.max_weight', '>', 0);

            if ($waterType !== 'all') {
                $heaviestFishQuery->where('fish_species.water_type', $waterType);
            }

            $heaviestFish = $heaviestFishQuery
                ->selectRaw('
                    fish_species.id as species_id,
                    fish_species.species,
                    fish_species.water_type,
                    fishing_logs.max_weight,
                    fishing_logs.date,
                    users.id as user_id,
                    users.name as user_name,
                    ROW_NUMBER() OVER (PARTITION BY fish_species.id ORDER BY fishing_logs.max_weight DESC) as rn
                ')
                ->get()
                ->where('rn', 1)
                ->keyBy('species_id');

            // OPTIMIZED: Get all most caught in one query
            $mostCaughtQuery = FishingLog::join('user_fish', 'fishing_logs.user_fish_id', '=', 'user_fish.id')
                ->join('users', 'fishing_logs.user_id', '=', 'users.id')
                ->join('fish_species', 'user_fish.fish_species_id', '=', 'fish_species.id')
                ->where('users.is_premium', true)
                ->whereBetween('fishing_logs.date', [$startDate, $endDate]);

            if ($waterType !== 'all') {
                $mostCaughtQuery->where('fish_species.water_type', $waterType);
            }

            $mostCaughtRaw = $mostCaughtQuery
                ->selectRaw('
                    fish_species.id as species_id,
                    fishing_logs.user_id,
                    users.name as user_name,
                    SUM(fishing_logs.quantity) as total_caught
                ')
                ->groupBy('fish_species.id', 'fishing_logs.user_id', 'users.name')
                ->get();

            // Get the top catcher for each species
            $mostCaught = $mostCaughtRaw
                ->groupBy('species_id')
                ->map(function ($group) {
                    return $group->sortByDesc('total_caught')->first();
                });

            // Get fish species based on water type filter
            $speciesQuery = FishSpecies::query();
            if ($waterType !== 'all') {
                $speciesQuery->where('water_type', $waterType);
            }
            $species = $speciesQuery->orderBy('species')->get();

            // Build leaderboard data by combining the results
            $leaderboard = $species->map(function ($fishSpecies) use ($biggestFish, $heaviestFish, $mostCaught) {
                $biggest = $biggestFish->get($fishSpecies->id);
                $heaviest = $heaviestFish->get($fishSpecies->id);
                $caught = $mostCaught->get($fishSpecies->id);

                return [
                    'species' => $fishSpecies->species,
                    'water_type' => $fishSpecies->water_type,
                    'biggest_fish' => $biggest ? [
                        'user_name' => $biggest->user_name,
                        'user_id' => $biggest->user_id,
                        'size' => (float) $biggest->max_size,
                        'date' => Carbon::parse($biggest->date)->format('M d, Y'),
                    ] : null,
                    'heaviest_fish' => $heaviest ? [
                        'user_name' => $heaviest->user_name,
                        'user_id' => $heaviest->user_id,
                        'weight' => (float) $heaviest->max_weight,
                        'date' => Carbon::parse($heaviest->date)->format('M d, Y'),
                    ] : null,
                    'most_caught' => $caught ? [
                        'user_name' => $caught->user_name,
                        'user_id' => $caught->user_id,
                        'total' => (int) $caught->total_caught,
                    ] : null,
                ];
            })->filter(function ($item) {
                // Only include species that have at least one premium leader
                return $item['biggest_fish'] !== null || $item['heaviest_fish'] !== null || $item['most_caught'] !== null;
            })->values();

            return $leaderboard;
        });

        // Generate month options (current month + previous 11 months)
        $monthOptions = collect(range(0, 11))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        });

        // Add last 10 years options
        $currentYear = now()->year;
        for ($i = 0; $i < 10; $i++) {
            $year = $currentYear - $i;
            $monthOptions->push([
                'value' => (string) $year,
                'label' => (string) $year,
            ]);
        }

        return Inertia::render('Leaderboard', [
            'leaderboard' => $leaderboard,
            'monthOptions' => $monthOptions,
            'selectedMonth' => $month,
            'selectedWaterType' => $waterType,
        ]);
    }
}
