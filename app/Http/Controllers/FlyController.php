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
}
