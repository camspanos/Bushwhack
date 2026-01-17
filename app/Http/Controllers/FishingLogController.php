<?php

namespace App\Http\Controllers;

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'location_id' => 'nullable|exists:locations,id',
            'fish_id' => 'nullable|exists:fish,id',
            'quantity' => 'nullable|integer|min:0',
            'max_size' => 'nullable|numeric|min:0',
            'fly_id' => 'nullable|exists:flies,id',
            'equipment_id' => 'nullable|exists:equipment,id',
            'style' => 'nullable|string|max:255',
            'friend_ids' => 'nullable|array',
            'friend_ids.*' => 'exists:friends,id',
            'notes' => 'nullable|string',
        ]);

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
