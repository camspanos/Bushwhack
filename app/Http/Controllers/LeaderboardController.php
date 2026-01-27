<?php

namespace App\Http\Controllers;

use App\Models\FishSpecies;
use App\Models\FishingLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // Get fish species based on water type filter
        $speciesQuery = FishSpecies::query();
        if ($waterType !== 'all') {
            $speciesQuery->where('water_type', $waterType);
        }
        $species = $speciesQuery->orderBy('species')->get();

        // Build leaderboard data for each species
        $leaderboard = $species->map(function ($fishSpecies) use ($startDate, $endDate) {
            // Get biggest fish for this species in the time period
            // Join through user_fish to get fish_species_id and users table to filter by premium status
            $biggestLog = FishingLog::join('user_fish', 'fishing_logs.fish_id', '=', 'user_fish.id')
                ->join('users', 'fishing_logs.user_id', '=', 'users.id')
                ->where('user_fish.fish_species_id', $fishSpecies->id)
                ->where('users.is_premium', true) // Only include premium users
                ->whereBetween('fishing_logs.date', [$startDate, $endDate])
                ->whereNotNull('fishing_logs.max_size')
                ->orderByDesc('fishing_logs.max_size')
                ->with('user')
                ->select('fishing_logs.*')
                ->first();

            // Get most caught for this species in the time period
            $mostCaughtData = FishingLog::join('user_fish', 'fishing_logs.fish_id', '=', 'user_fish.id')
                ->join('users', 'fishing_logs.user_id', '=', 'users.id')
                ->where('user_fish.fish_species_id', $fishSpecies->id)
                ->where('users.is_premium', true) // Only include premium users
                ->whereBetween('fishing_logs.date', [$startDate, $endDate])
                ->selectRaw('fishing_logs.user_id, SUM(fishing_logs.quantity) as total_caught')
                ->groupBy('fishing_logs.user_id')
                ->orderByDesc('total_caught')
                ->first();

            // Load the user for most caught (can't use with() on grouped queries)
            $mostCaughtUser = null;
            if ($mostCaughtData) {
                $mostCaughtUser = \App\Models\User::find($mostCaughtData->user_id);
            }

            return [
                'species' => $fishSpecies->species,
                'water_type' => $fishSpecies->water_type,
                'biggest_fish' => $biggestLog && $biggestLog->user->is_premium ? [
                    'user_name' => $biggestLog->user->name,
                    'user_id' => $biggestLog->user->id,
                    'size' => $biggestLog->max_size,
                    'date' => $biggestLog->date->format('M d, Y'),
                ] : null,
                'most_caught' => $mostCaughtData && $mostCaughtUser && $mostCaughtUser->is_premium ? [
                    'user_name' => $mostCaughtUser->name,
                    'user_id' => $mostCaughtUser->id,
                    'total' => $mostCaughtData->total_caught,
                ] : null,
            ];
        })->filter(function ($item) {
            // Only include species that have at least one premium leader
            return $item['biggest_fish'] !== null || $item['most_caught'] !== null;
        })->values();

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
