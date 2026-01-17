<?php

namespace App\Http\Controllers;

use App\Models\Fly;
use Illuminate\Http\Request;

class FlyController extends Controller
{
    public function index()
    {
        $flies = Fly::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($flies);
    }

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
