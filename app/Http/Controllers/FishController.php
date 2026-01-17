<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use Illuminate\Http\Request;

class FishController extends Controller
{
    public function index()
    {
        $fish = Fish::where('user_id', auth()->id())
            ->orderBy('species')
            ->get();

        return response()->json($fish);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'species' => 'required|string|max:255',
            'water_type' => 'nullable|string|max:255',
        ]);

        $fish = Fish::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($fish, 201);
    }
}
