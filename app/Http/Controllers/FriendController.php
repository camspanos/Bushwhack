<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFriendRequest;
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
     * @param  \App\Http\Requests\StoreFriendRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreFriendRequest $request)
    {
        $friend = Friend::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($friend, 201);
    }
}
