<?php

use App\Http\Controllers\Settings\FollowingSettingsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SubscriptionController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/following', [FollowingSettingsController::class, 'edit'])->name('following-settings.edit');
    Route::patch('settings/following', [FollowingSettingsController::class, 'update'])->name('following-settings.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    // Subscription routes
    Route::get('settings/subscription', [SubscriptionController::class, 'edit'])->name('subscription.edit');
    Route::post('settings/subscription/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('settings/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::delete('settings/subscription', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

// Webhook route (no auth required)
Route::post('webhooks/payment', [SubscriptionController::class, 'webhook'])->name('webhooks.payment');
