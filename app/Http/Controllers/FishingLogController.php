<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFishingLogRequest;
use App\Models\FishingLog;
use Illuminate\Http\Request;

class FishingLogController extends Controller
{
    /**
     * Store a newly created fishing log in storage.
     *
     * Creates a new fishing log entry for the authenticated user and associates
     * it with the specified friends using a many-to-many relationship through
     * the fishing_log_friend pivot table.
     *
     * @param  \App\Http\Requests\StoreFishingLogRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreFishingLogRequest $request)
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
}
