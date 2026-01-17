<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the authenticated user's locations.
     *
     * Retrieves all locations belonging to the authenticated user,
     * ordered alphabetically by name.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $locations = Location::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($locations);
    }

    /**
     * Store a newly created location in storage.
     *
     * Creates a new location record for the authenticated user with the
     * provided name, city, state, and country information.
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
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $location = Location::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($location, 201);
    }
}
