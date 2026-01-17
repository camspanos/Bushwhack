<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlyRequest;
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
     * @param  \App\Http\Requests\StoreFlyRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreFlyRequest $request)
    {
        $fly = Fly::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($fly, 201);
    }
}
