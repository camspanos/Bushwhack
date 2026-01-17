<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the authenticated user's equipment.
     *
     * Retrieves all equipment records belonging to the authenticated user,
     * ordered alphabetically by rod name. Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $equipment = Equipment::where('user_id', auth()->id())
            ->orderBy('rod_name')
            ->paginate($perPage);

        return response()->json($equipment);
    }

    /**
     * Store a newly created equipment in storage.
     *
     * Creates a new equipment record for the authenticated user with the
     * provided rod name, rod weight, lure, line, and tippet information.
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $equipment = Equipment::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($equipment, 201);
    }

    /**
     * Update the specified equipment in storage.
     *
     * Updates an equipment record for the authenticated user.
     */
    public function update(StoreEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        // Ensure the user owns this equipment
        if ($equipment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $equipment->update($request->validated());

        return response()->json($equipment);
    }

    /**
     * Remove the specified equipment from storage.
     *
     * Deletes an equipment entry for the authenticated user.
     */
    public function destroy(Equipment $equipment): JsonResponse
    {
        // Ensure the user owns this equipment
        if ($equipment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $equipment->delete();

        return response()->json(['message' => 'Equipment deleted successfully']);
    }
}
