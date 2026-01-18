<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlyRequest;
use App\Models\Fly;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlyController extends Controller
{
    /**
     * Display a listing of the authenticated user's flies.
     *
     * Retrieves all fly records belonging to the authenticated user,
     * ordered alphabetically by name. Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $flies = Fly::where('user_id', auth()->id())
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($flies);
    }

    /**
     * Store a newly created fly in storage.
     *
     * Creates a new fly record for the authenticated user with the
     * provided name, color, size, and type information.
     */
    public function store(StoreFlyRequest $request): JsonResponse
    {
        $fly = Fly::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($fly, 201);
    }

    /**
     * Update the specified fly in storage.
     *
     * Updates a fly record for the authenticated user.
     */
    public function update(StoreFlyRequest $request, Fly $fly): JsonResponse
    {
        // Ensure the user owns this fly
        if ($fly->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fly->update($request->validated());

        return response()->json($fly);
    }

    /**
     * Remove the specified fly from storage.
     *
     * Deletes a fly entry for the authenticated user.
     */
    public function destroy(Fly $fly): JsonResponse
    {
        // Ensure the user owns this fly
        if ($fly->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fly->delete();

        return response()->json(['message' => 'Fly deleted successfully']);
    }

    /**
     * Get statistics for all flies.
     *
     * Returns aggregated statistics for each fly including:
     * - Total caught
     * - Total trips
     * - Biggest fish
     * - Success rate
     */
    public function statistics(Request $request): JsonResponse
    {
        $yearFilter = $request->input('year', 'lifetime');

        $flies = Fly::where('user_id', auth()->id())
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('id', 'fly_id', 'quantity', 'max_size', 'date');
                if ($yearFilter !== 'lifetime') {
                    $query->whereYear('date', $yearFilter);
                }
            }])
            ->get()
            ->map(function ($fly) {
                $logs = $fly->fishingLogs;
                $totalCaught = $logs->sum('quantity');
                $totalTrips = $logs->count();
                $biggestFish = $logs->max('max_size') ?? 0;
                $successfulTrips = $logs->where('quantity', '>', 0)->count();
                $successRate = $totalTrips > 0
                    ? round(($successfulTrips / $totalTrips) * 100, 1)
                    : 0;

                return [
                    'id' => $fly->id,
                    'name' => $fly->name,
                    'color' => $fly->color,
                    'size' => $fly->size,
                    'type' => $fly->type,
                    'totalCaught' => $totalCaught,
                    'totalTrips' => $totalTrips,
                    'biggestFish' => $biggestFish,
                    'successRate' => $successRate,
                ];
            })
            ->sortByDesc('totalCaught')
            ->values();

        return response()->json($flies);
    }
}
