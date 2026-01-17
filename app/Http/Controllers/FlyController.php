<?php

namespace App\Http\Controllers;

use App\Models\Fly;
use Illuminate\Http\Request;

class FlyController extends Controller
{
    /**
     * Display a listing of the authenticated user's flies.
     *
     * Retrieves all fly records belonging to the authenticated user,
     * ordered alphabetically by name.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $flies = Fly::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($flies);
    }

    /**
     * Store a newly created fly in storage.
     *
     * Creates a new fly record for the authenticated user with the
     * provided name, color, size, and type information.
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
            'color' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
        ]);

        $fly = Fly::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($fly, 201);
    }
}
