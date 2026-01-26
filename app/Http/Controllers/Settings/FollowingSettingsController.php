<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowingSettingsController extends Controller
{
    /**
     * Display the following settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/FollowingSettings', [
            'allowFollowers' => $request->user()->allow_followers,
        ]);
    }

    /**
     * Update the following settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'allow_followers' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->update($validated);

        // If followers are disabled, remove all existing followers
        if (!$validated['allow_followers']) {
            $user->followers()->detach();
        }

        return back();
    }
}
