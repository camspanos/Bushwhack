<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    /**
     * Display a listing of the authenticated user's friends.
     *
     * Retrieves all friend records belonging to the authenticated user,
     * ordered alphabetically by name.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $friends = Friend::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($friends);
    }

    /**
     * Store a newly created friend in storage.
     *
     * Creates a new friend record for the authenticated user with the
     * provided name. Friends can be associated with fishing logs through
     * a many-to-many relationship.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $friend = Friend::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($friend, 201);
    }
}
