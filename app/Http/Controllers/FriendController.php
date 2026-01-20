<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFriendRequest;
use App\Models\Friend;
use App\Models\FishingLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    /**
     * Display a listing of the authenticated user's friends.
     *
     * Retrieves all friend records belonging to the authenticated user,
     * ordered alphabetically by name. Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $friends = Friend::where('user_id', auth()->id())
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($friends);
    }

    /**
     * Store a newly created friend in storage.
     *
     * Creates a new friend record for the authenticated user with the
     * provided name. Friends can be associated with fishing logs through
     * a many-to-many relationship.
     */
    public function store(StoreFriendRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Check if friend already exists with same name
            $existing = Friend::where('user_id', auth()->id())
                ->where('name', $validated['name'])
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'A friend with this name already exists.',
                    'friend' => $existing
                ], 409);
            }

            $friend = Friend::create([
                'user_id' => auth()->id(),
                ...$validated,
            ]);

            return response()->json($friend, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating friend: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Failed to create friend. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified friend in storage.
     *
     * Updates a friend record for the authenticated user.
     */
    public function update(StoreFriendRequest $request, Friend $friend): JsonResponse
    {
        // Ensure the user owns this friend
        if ($friend->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        // Check if another friend already exists with same name
        $existing = Friend::where('user_id', auth()->id())
            ->where('id', '!=', $friend->id)
            ->where('name', $validated['name'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'A friend with this name already exists.',
            ], 409);
        }

        $friend->update($validated);

        return response()->json($friend);
    }

    /**
     * Remove the specified friend from storage.
     *
     * Deletes a friend entry for the authenticated user.
     */
    public function destroy(Friend $friend): JsonResponse
    {
        // Ensure the user owns this friend
        if ($friend->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $friend->delete();

        return response()->json(['message' => 'Friend deleted successfully']);
    }

    /**
     * Get statistics for all friends.
     *
     * Returns aggregated statistics for each friend including:
     * - Total trips together
     * - Total fish caught
     * - Biggest fish
     * - Success rate
     */
    public function statistics(Request $request): JsonResponse
    {
        $yearFilter = $request->input('year', 'lifetime');

        $friends = Friend::where('user_id', auth()->id())
            ->with(['fishingLogs' => function ($query) use ($yearFilter) {
                $query->select('fishing_logs.id', 'quantity', 'max_size', 'date');
                if ($yearFilter !== 'lifetime') {
                    $query->whereYear('date', $yearFilter);
                }
            }])
            ->get()
            ->map(function ($friend) {
                $logs = $friend->fishingLogs;
                $totalTrips = $logs->count();
                $totalFish = $logs->sum('quantity');
                $biggestFish = $logs->max('max_size') ?? 0;
                $successfulTrips = $logs->where('quantity', '>', 0)->count();
                $successRate = $totalTrips > 0
                    ? round(($successfulTrips / $totalTrips) * 100, 1)
                    : 0;

                return [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'totalTrips' => $totalTrips,
                    'totalFish' => $totalFish,
                    'biggestFish' => $biggestFish,
                    'successRate' => $successRate,
                ];
            })
            ->filter(function ($friend) {
                // Only include friends that have trips in the filtered date range
                return $friend['totalTrips'] > 0;
            })
            ->sortByDesc('totalTrips')
            ->values();

        return response()->json($friends);
    }
}
