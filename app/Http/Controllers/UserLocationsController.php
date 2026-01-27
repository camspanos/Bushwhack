<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserLocationRequest;
use App\Models\UserLocation;
use App\Models\FishingLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserLocationsController extends Controller
{
    /**
     * Display a listing of the authenticated user's locations.
     *
     * Retrieves all locations belonging to the authenticated user,
     * ordered alphabetically by name. Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $locations = UserUserLocation::where('user_id', auth()->id())
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($locations);
    }

    /**
     * Store a newly created location in storage.
     *
     * Creates a new location record for the authenticated user with the
     * provided name, city, state, and country information.
     */
    public function store(StoreUserLocationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Check if location already exists with same details
            $existing = UserUserLocation::where('user_id', auth()->id())
                ->where('name', $validated['name'])
                ->where('city', $validated['city'] ?? null)
                ->where('state', $validated['state'] ?? null)
                ->where('country', $validated['country'] ?? null)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'A location with these details already exists.',
                    'location' => $existing
                ], 409);
            }

            $location = UserUserLocation::create([
                'user_id' => auth()->id(),
                ...$validated,
            ]);

            return response()->json($location, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating location: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Failed to create location. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified location in storage.
     *
     * Updates a location record for the authenticated user.
     */
    public function update(StoreUserLocationRequest $request, Location $location): JsonResponse
    {
        // Ensure the user owns this location
        if ($location->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        // Check if another location already exists with same details
        $existing = UserUserLocation::where('user_id', auth()->id())
            ->where('id', '!=', $location->id)
            ->where('name', $validated['name'])
            ->where('city', $validated['city'] ?? null)
            ->where('state', $validated['state'] ?? null)
            ->where('country', $validated['country'] ?? null)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'A location with these details already exists.',
            ], 409);
        }

        $location->update($validated);

        return response()->json($location);
    }

    /**
     * Remove the specified location from storage.
     *
     * Deletes a location entry for the authenticated user.
     */
    public function destroy(Location $location): JsonResponse
    {
        // Ensure the user owns this location
        if ($location->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $location->delete();

        return response()->json(['message' => 'Location deleted successfully']);
    }

    /**
     * Get statistics for all locations.
     *
     * Returns aggregated statistics for each location including:
     * - Total trips
     * - Total fish caught
     * - Biggest fish
     * - Success rate
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

        $locations = UserUserLocation::where('user_id', auth()->id())
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'user_location_id', 'quantity', 'max_size', 'date');
                if ($yearFilter !== 'lifetime') {
                    $query->whereYear('date', $yearFilter);
                }
            }])
            ->get()
            ->map(function ($location) {
                $logs = $location->fishingLogs;
                $totalTrips = $logs->pluck('date')->unique()->count();
                $totalFish = $logs->sum('quantity');
                $biggestFish = $logs->max('max_size') ?? 0;
                $daysWithFish = $logs->where('quantity', '>', 0)->pluck('date')->unique()->count();
                $successRate = $totalTrips > 0
                    ? round(($daysWithFish / $totalTrips) * 100, 1)
                    : 0;

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'city' => $location->city,
                    'state' => $location->state,
                    'country' => $location->country,
                    'totalTrips' => $totalTrips,
                    'totalFish' => $totalFish,
                    'biggestFish' => (float) $biggestFish,
                    'successRate' => $successRate,
                ];
            })
            ->filter(function ($location) {
                // Only include locations that have trips in the filtered date range
                return $location['totalTrips'] > 0;
            })
            ->sortByDesc('totalFish')
            ->values();

        return response()->json($locations);
    }
}
