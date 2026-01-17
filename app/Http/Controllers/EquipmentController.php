<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::where('user_id', auth()->id())
            ->orderBy('rod_name')
            ->get();

        return response()->json($equipment);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rod_name' => 'required|string|max:255',
            'rod_weight' => 'nullable|string|max:255',
            'lure' => 'nullable|string|max:255',
            'line' => 'nullable|string|max:255',
            'tippet' => 'nullable|string|max:255',
        ]);

        $equipment = Equipment::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($equipment, 201);
    }
}
