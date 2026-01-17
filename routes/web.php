<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\FishController;
use App\Http\Controllers\FishingLogController;
use App\Http\Controllers\FlyController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('fishing-log', function () {
    return Inertia::render('FishingLog');
})->middleware(['auth', 'verified'])->name('fishing-log');

// CRUD Routes for Fishing Log Resources
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('locations', LocationController::class);
    Route::resource('equipment', EquipmentController::class);
    Route::resource('fish', FishController::class);
    Route::resource('flies', FlyController::class);
    Route::resource('friends', FriendController::class);
    Route::resource('fishing-logs', FishingLogController::class);
});

require __DIR__.'/settings.php';
