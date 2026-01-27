<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserFishRequest;
use App\Models\UserFish;
use App\Models\FishSpecies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserFishController extends Controller
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

        $fish = UserFish::where('user_id', auth()->id())
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
    public function store(StoreUserFishRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Check if fish species already exists with same details
            $existing = UserFish::where('user_id', auth()->id())
                ->where('species', $validated['species'])
                ->where('water_type', $validated['water_type'] ?? null)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'A fish species with these details already exists.',
                    'fish' => $existing
                ], 409);
            }

            // Try to link to global fish species
            $fishSpeciesId = $this->findMatchingFishSpecies($validated['species'], $validated['water_type'] ?? null);

            $fish = UserFish::create([
                'user_id' => auth()->id(),
                'fish_species_id' => $fishSpeciesId,
                ...$validated,
            ]);

            return response()->json($fish, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating fish species: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Failed to create fish species. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified fish species in storage.
     *
     * Updates a fish species record for the authenticated user.
     */
    public function update(StoreUserFishRequest $request, Fish $fish): JsonResponse
    {
        // Ensure the user owns this fish species
        if ($fish->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        // Check if another fish species already exists with same details
        $existing = UserFish::where('user_id', auth()->id())
            ->where('id', '!=', $fish->id)
            ->where('species', $validated['species'])
            ->where('water_type', $validated['water_type'] ?? null)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'A fish species with these details already exists.',
            ], 409);
        }

        // Try to link to global fish species
        $fishSpeciesId = $this->findMatchingFishSpecies($validated['species'], $validated['water_type'] ?? null);

        $fish->update([
            'fish_species_id' => $fishSpeciesId,
            ...$validated,
        ]);

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
        // Free users can only view current year data
        $user = auth()->user();
        if (!$user->canFilterByYear()) {
            $yearFilter = (string) now()->year;
        } else {
            $yearFilter = $request->input('year', 'lifetime');
        }

        $fish = UserFish::where('user_id', auth()->id())
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

        return response()->json($fish);
    }

    /**
     * Find a matching global fish species based on species name.
     *
     * Attempts to match the user's fish species name to a global fish species
     * using case-insensitive partial matching. If a match is found, returns
     * the fish_species_id, otherwise returns null.
     *
     * Note: This does NOT create new entries in fish_species table.
     * Only the seeder should populate fish_species.
     */
    private function findMatchingFishSpecies(string $species, ?string $waterType): ?int
    {
        // Try exact match first
        $fishSpecies = FishSpecies::whereRaw('LOWER(species) = ?', [strtolower($species)])
            ->first();

        if ($fishSpecies) {
            return $fishSpecies->id;
        }

        // Try partial match - check if user's species contains global species name
        $fishSpecies = FishSpecies::whereRaw('LOWER(?) LIKE CONCAT("%", LOWER(species), "%")', [$species])
            ->first();

        if ($fishSpecies) {
            return $fishSpecies->id;
        }

        // Try reverse partial match - check if global species contains user's species name
        $fishSpecies = FishSpecies::whereRaw('LOWER(species) LIKE ?', ['%' . strtolower($species) . '%'])
            ->first();

        if ($fishSpecies) {
            return $fishSpecies->id;
        }

        // If no match found, return null (do not create new fish_species entries)
        // Only the seeder should populate the fish_species table
        return null;
    }
}
