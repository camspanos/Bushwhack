<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFishingLogRequest;
use App\Models\FishingLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FishingLogController extends Controller
{
    /**
     * Display a listing of the authenticated user's fishing logs.
     *
     * Retrieves all fishing log records belonging to the authenticated user,
     * ordered by date (most recent first), with related data loaded.
     */
    public function index(): JsonResponse
    {
        $fishingLogs = FishingLog::where('user_id', auth()->id())
            ->with(['location', 'fish', 'fly', 'equipment', 'friends'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($fishingLogs);
    }

    /**
     * Store a newly created fishing log in storage.
     *
     * Creates a new fishing log entry for the authenticated user and associates
     * it with the specified friends using a many-to-many relationship through
     * the fishing_log_friend pivot table.
     */
    public function store(StoreFishingLogRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Create the fishing log
        $fishingLog = FishingLog::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'location_id' => $validated['location_id'] ?? null,
            'fish_id' => $validated['fish_id'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'max_size' => $validated['max_size'] ?? null,
            'fly_id' => $validated['fly_id'] ?? null,
            'equipment_id' => $validated['equipment_id'] ?? null,
            'style' => $validated['style'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Attach friends if provided
        if (!empty($validated['friend_ids'])) {
            $fishingLog->friends()->attach($validated['friend_ids']);
        }

        return response()->json($fishingLog->load('friends'), 201);
    }

    /**
     * Update the specified fishing log in storage.
     *
     * Updates an existing fishing log entry for the authenticated user and
     * syncs the associated friends.
     */
    public function update(StoreFishingLogRequest $request, FishingLog $fishingLog): JsonResponse
    {
        // Ensure the user owns this fishing log
        if ($fishingLog->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        // Update the fishing log
        $fishingLog->update([
            'date' => $validated['date'],
            'location_id' => $validated['location_id'] ?? null,
            'fish_id' => $validated['fish_id'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'max_size' => $validated['max_size'] ?? null,
            'fly_id' => $validated['fly_id'] ?? null,
            'equipment_id' => $validated['equipment_id'] ?? null,
            'style' => $validated['style'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sync friends (this will remove old associations and add new ones)
        if (isset($validated['friend_ids'])) {
            $fishingLog->friends()->sync($validated['friend_ids']);
        } else {
            $fishingLog->friends()->sync([]);
        }

        return response()->json($fishingLog->load(['location', 'fish', 'fly', 'equipment', 'friends']));
    }
}
