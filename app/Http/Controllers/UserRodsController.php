<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRodRequest;
use App\Models\UserRod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserRodsController extends Controller
{
    /**
     * Display a listing of the authenticated user's rods.
     *
     * Retrieves all rod records belonging to the authenticated user,
     * ordered alphabetically by rod name. Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $rods = UserRod::where('user_id', auth()->id())
            ->orderBy('rod_name')
            ->paginate($perPage);

        return response()->json($rods);
    }

    /**
     * Store a newly created rod in storage.
     *
     * Creates a new rod record for the authenticated user with the
     * provided rod name, rod weight, rod length, reel, and line information.
     */
    public function store(StoreUserRodRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Check if rod already exists with same details
            $existing = UserRod::where('user_id', auth()->id())
                ->where('rod_name', $validated['rod_name'])
                ->where('rod_weight', $validated['rod_weight'] ?? null)
                ->where('rod_length', $validated['rod_length'] ?? null)
                ->where('reel', $validated['reel'] ?? null)
                ->where('line', $validated['line'] ?? null)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'A rod with these details already exists.',
                    'rod' => $existing
                ], 409);
            }

            $rod = UserRod::create([
                'user_id' => auth()->id(),
                ...$validated,
            ]);

            return response()->json($rod, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating rod: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Failed to create rod. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified rod in storage.
     *
     * Updates a rod record for the authenticated user.
     */
    public function update(StoreUserRodRequest $request, Rod $rod): JsonResponse
    {
        // Ensure the user owns this rod
        if ($rod->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        // Check if another rod already exists with same details
        $existing = UserRod::where('user_id', auth()->id())
            ->where('id', '!=', $rod->id)
            ->where('rod_name', $validated['rod_name'])
            ->where('rod_weight', $validated['rod_weight'] ?? null)
            ->where('rod_length', $validated['rod_length'] ?? null)
            ->where('reel', $validated['reel'] ?? null)
            ->where('line', $validated['line'] ?? null)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'A rod with these details already exists.',
            ], 409);
        }

        $rod->update($validated);

        return response()->json($rod);
    }

    /**
     * Remove the specified rod from storage.
     *
     * Deletes a rod entry for the authenticated user.
     */
    public function destroy(Rod $rod): JsonResponse
    {
        // Ensure the user owns this rod
        if ($rod->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rod->delete();

        return response()->json(['message' => 'Rod deleted successfully']);
    }

    /**
     * Get statistics for all rods.
     *
     * Returns aggregated statistics for each rod including:
     * - Total trips
     * - Total fish caught
     * - Biggest fish
     * - Success rate
     */
    public function statistics(Request $request): JsonResponse
    {
        // Free users can only view current year data
        $user = auth()->user();
        $userId = auth()->id();

        if (!$user->canFilterByYear()) {
            $yearFilter = (string) now()->year;
        } else {
            $yearFilter = $request->input('year', 'lifetime');
        }

        // Create cache key
        $cacheKey = "rods_stats_{$userId}_{$yearFilter}";

        // Cache data for 1 hour
        $rods = Cache::remember($cacheKey, 3600, function () use ($userId, $yearFilter) {
            return UserRod::where('user_id', $userId)
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'user_rod_id', 'quantity', 'max_size', 'date');
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
        });

        return response()->json($rods);
    }
}

