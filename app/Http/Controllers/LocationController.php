<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($locations);
    }

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
