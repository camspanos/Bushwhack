<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the authenticated user's equipment.
     *
     * Retrieves all equipment records belonging to the authenticated user,
     * ordered alphabetically by rod name.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $equipment = Equipment::where('user_id', auth()->id())
            ->orderBy('rod_name')
            ->get();

        return response()->json($equipment);
    }

    /**
     * Store a newly created equipment in storage.
     *
     * Creates a new equipment record for the authenticated user with the
     * provided rod name, rod weight, lure, line, and tippet information.
     *
     * @param  \App\Http\Requests\StoreEquipmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEquipmentRequest $request)
    {
        $equipment = Equipment::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return response()->json($equipment, 201);
    }
}
