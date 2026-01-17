<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFriendRequest;
use App\Models\Friend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $friend = Friend::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($friend, 201);
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

        $friend->update($request->validated());

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
}
