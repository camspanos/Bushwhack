<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserRodsController;
use App\Http\Controllers\UserFishController;
use App\Http\Controllers\FishingLogsController;
use App\Http\Controllers\UserFliesController;
use App\Http\Controllers\FollowingController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\UserLocationsController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\PublicRodsController;
use App\Http\Controllers\PublicFishController;
use App\Http\Controllers\PublicFliesController;
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

Route::get('rods-page', function () {
    return Inertia::render('Rod');
})->middleware(['auth', 'verified'])->name('rods-page');

Route::get('fish-page', function () {
    return Inertia::render('Fish');
})->middleware(['auth', 'verified'])->name('fish-page');

Route::get('flies-page', function () {
    return Inertia::render('Flies');
})->middleware(['auth', 'verified'])->name('flies-page');

Route::get('friends-page', function () {
    return Inertia::render('Friends');
})->middleware(['auth', 'verified'])->name('friends-page');

Route::get('about', function () {
    return Inertia::render('About');
})->middleware(['auth', 'verified'])->name('about');

// Leaderboard Route (public)
Route::get('leaderboard', [App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard');

// Following Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('following', [FollowingController::class, 'index'])->name('following');
    Route::post('users/{user}/follow', [FollowingController::class, 'follow'])->name('users.follow');
    Route::delete('users/{user}/unfollow', [FollowingController::class, 'unfollow'])->name('users.unfollow');
    Route::get('users/search', [FollowingController::class, 'search'])->name('users.search');
    Route::get('users/{user}/dashboard', [PublicDashboardController::class, 'show'])->name('users.dashboard');
    Route::get('users/{user}/rods', [PublicRodsController::class, 'index'])->name('users.rods');
    Route::get('users/{user}/fish', [PublicFishController::class, 'index'])->name('users.fish');
    Route::get('users/{user}/flies', [PublicFliesController::class, 'index'])->name('users.flies');
});

// CRUD Routes for Fishing Log Resources
Route::middleware(['auth', 'verified'])->group(function () {
    // Statistics Routes (must be before resource routes to avoid conflicts)
    Route::get('locations/stats/all', [UserLocationsController::class, 'statistics'])->name('locations.statistics');
    Route::get('rods/stats/all', [UserRodsController::class, 'statistics'])->name('rods.statistics');
    Route::get('fish/stats/all', [UserFishController::class, 'statistics'])->name('fish.statistics');
    Route::get('flies/stats/all', [UserFliesController::class, 'statistics'])->name('flies.statistics');
    Route::get('friends/stats/all', [FriendController::class, 'statistics'])->name('friends.statistics');

    // Utility Routes (must be before resource routes to avoid conflicts)
    Route::get('fishing-logs/available-years', [FishingLogsController::class, 'availableYears'])->name('fishing-logs.available-years');

    // Resource Routes
    Route::resource('locations', UserLocationsController::class);
    Route::resource('rods', UserRodsController::class);
    Route::resource('fish', UserFishController::class);
    Route::resource('flies', UserFliesController::class);
    Route::resource('friends', FriendController::class);
    Route::resource('fishing-logs', FishingLogsController::class);
});

require __DIR__.'/settings.php';
