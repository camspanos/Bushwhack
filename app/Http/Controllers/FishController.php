<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use Illuminate\Http\Request;

class FishController extends Controller
{
    /**
     * Display a listing of the authenticated user's fish species.
     *
     * Retrieves all fish species records belonging to the authenticated user,
     * ordered alphabetically by species name.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $fish = Fish::where('user_id', auth()->id())
            ->orderBy('species')
            ->get();

        return response()->json($fish);
    }

    /**
     * Store a newly created fish species in storage.
     *
     * Creates a new fish species record for the authenticated user with the
     * provided species name and water type information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
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
