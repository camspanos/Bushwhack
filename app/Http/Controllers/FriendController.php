<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function index()
    {
        $friends = Friend::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($friends);
    }

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
