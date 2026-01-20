<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFishRequest;
use App\Models\Fish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FishController extends Controller
{
    /**
     * Display a listing of the authenticated user's fish species.
     *
     * Retrieves all fish species records belonging to the authenticated user,
     * ordered alphabetically by species name. Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $fish = Fish::where('user_id', auth()->id())
            ->orderBy('species')
            ->paginate($perPage);

        return response()->json($fish);
    }

    /**
     * Store a newly created fish species in storage.
     *
     * Creates a new fish species record for the authenticated user with the
     * provided species name and water type information.
     */
    public function store(StoreFishRequest $request): JsonResponse
    {
        $fish = Fish::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($fish, 201);
    }

    /**
     * Update the specified fish species in storage.
     *
     * Updates a fish species record for the authenticated user.
     */
    public function update(StoreFishRequest $request, Fish $fish): JsonResponse
    {
        // Ensure the user owns this fish species
        if ($fish->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fish->update($request->validated());

        return response()->json($fish);
    }

    /**
     * Remove the specified fish species from storage.
     *
     * Deletes a fish species entry for the authenticated user.
     */
    public function destroy(Fish $fish): JsonResponse
    {
        // Ensure the user owns this fish species
        if ($fish->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fish->delete();

        return response()->json(['message' => 'Fish species deleted successfully']);
    }

    /**
     * Get statistics for all fish species.
     *
     * Returns aggregated statistics for each fish species including:
     * - Total caught
     * - Total trips
     * - Biggest fish
     * - Average size
     */
    public function statistics(Request $request): JsonResponse
    {
        $yearFilter = $request->input('year', 'lifetime');

        $fish = Fish::where('user_id', auth()->id())
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'fish_id', 'quantity', 'max_size', 'date');
                if ($yearFilter !== 'lifetime') {
                    $query->whereYear('date', $yearFilter);
                }
            }])
            ->get()
            ->map(function ($fishSpecies) {
                $logs = $fishSpecies->fishingLogs;
                $totalCaught = $logs->sum('quantity');
                $totalTrips = $logs->count();
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

        return response()->json($fish);
    }
}
