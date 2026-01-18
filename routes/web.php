<?php

use App\Http\Controllers\DashboardController;
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

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('fishing-log', function () {
    return Inertia::render('FishingLog');
})->middleware(['auth', 'verified'])->name('fishing-log');

Route::get('fishing-log/create', function () {
    return Inertia::render('FishingLogCreate');
})->middleware(['auth', 'verified'])->name('fishing-log.create');

Route::get('locations-page', function () {
    return Inertia::render('Locations');
})->middleware(['auth', 'verified'])->name('locations-page');

Route::get('equipment-page', function () {
    return Inertia::render('Equipment');
})->middleware(['auth', 'verified'])->name('equipment-page');

Route::get('fish-page', function () {
    return Inertia::render('Fish');
})->middleware(['auth', 'verified'])->name('fish-page');

Route::get('flies-page', function () {
    return Inertia::render('Flies');
})->middleware(['auth', 'verified'])->name('flies-page');

Route::get('friends-page', function () {
    return Inertia::render('Friends');
})->middleware(['auth', 'verified'])->name('friends-page');

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
