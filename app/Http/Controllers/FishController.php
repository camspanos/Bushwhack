<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFishRequest;
use App\Models\Fish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FishController extends Controller
{
    /**
     * Display a listing of the authenticated user's fish species.
     *
     * Retrieves all fish species records belonging to the authenticated user,
     * ordered alphabetically by species name.
     */
    public function index(): JsonResponse
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
     */
    public function store(StoreFishRequest $request): JsonResponse
    {
        $fish = Fish::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($fish, 201);
    }
}
