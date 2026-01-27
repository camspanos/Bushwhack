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
     * Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $fishingLogs = FishingLog::where('user_id', auth()->id())
            ->with(['location', 'fish', 'fly', 'equipment', 'friends'])
            ->orderBy('date', 'desc')
            ->paginate($perPage);

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

        // Check if this is a new species for the user
        $isNewSpecies = false;
        $isPersonalBest = false;
        $previousBestSize = null;

        if (!empty($validated['fish_id'])) {
            $previousCatch = FishingLog::where('user_id', auth()->id())
                ->where('fish_id', $validated['fish_id'])
                ->exists();

            $isNewSpecies = !$previousCatch;

            // Check for personal best (only if not a new species and max_size is provided)
            if (!$isNewSpecies && !empty($validated['max_size'])) {
                $previousBest = FishingLog::where('user_id', auth()->id())
                    ->where('fish_id', $validated['fish_id'])
                    ->whereNotNull('max_size')
                    ->max('max_size');

                if ($previousBest !== null && $validated['max_size'] > $previousBest) {
                    $isPersonalBest = true;
                    $previousBestSize = $previousBest;
                }
            }
        }

        // Create the fishing log
        $fishingLog = FishingLog::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'location_id' => $validated['location_id'] ?? null,
            'fish_id' => $validated['fish_id'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'max_size' => $validated['max_size'] ?? null,
            'fly_id' => $validated['fly_id'] ?? null,
            'equipment_id' => $validated['equipment_id'] ?? null,
            'style' => $validated['style'] ?? null,
            'moon_phase' => $validated['moon_phase'] ?? null,
            'barometric_pressure' => $validated['barometric_pressure'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Attach friends if provided
        if (!empty($validated['friend_ids'])) {
            $fishingLog->friends()->attach($validated['friend_ids']);
        }

        return response()->json([
            'fishing_log' => $fishingLog->load(['friends', 'fish']),
            'is_new_species' => $isNewSpecies,
            'is_personal_best' => $isPersonalBest,
            'previous_best_size' => $previousBestSize,
        ], 201);
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
            'time' => $validated['time'] ?? null,
            'location_id' => $validated['location_id'] ?? null,
            'fish_id' => $validated['fish_id'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'max_size' => $validated['max_size'] ?? null,
            'fly_id' => $validated['fly_id'] ?? null,
            'equipment_id' => $validated['equipment_id'] ?? null,
            'style' => $validated['style'] ?? null,
            'moon_phase' => $validated['moon_phase'] ?? null,
            'barometric_pressure' => $validated['barometric_pressure'] ?? null,
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

    /**
     * Remove the specified fishing log from storage.
     *
     * Deletes a fishing log entry for the authenticated user.
     */
    public function destroy(FishingLog $fishingLog): JsonResponse
    {
        // Ensure the user owns this fishing log
        if ($fishingLog->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the fishing log (friends will be automatically detached due to cascade)
        $fishingLog->delete();

        return response()->json(['message' => 'Fishing log deleted successfully']);
    }

    /**
     * Get available years from fishing logs.
     *
     * Returns a list of distinct years from the user's fishing logs,
     * ordered from most recent to oldest. Always includes current year.
     */
    public function availableYears(): JsonResponse
    {
        $years = FishingLog::where('user_id', auth()->id())
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->map(fn($year) => (string) $year)
            ->values()
            ->toArray();

        // Always include current year even if no data exists for it
        $currentYear = (string) now()->year;
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        return response()->json($years);
    }
}
