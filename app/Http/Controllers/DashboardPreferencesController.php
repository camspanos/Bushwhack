<?php

namespace App\Http\Controllers;

use App\Models\UserDashboardPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardPreferencesController extends Controller
{
    /**
     * Get the user's dashboard preferences.
     */
    public function index(): JsonResponse
    {
        $userId = auth()->id();
        
        $preferences = UserDashboardPreference::where('user_id', $userId)
            ->orderBy('order')
            ->get()
            ->keyBy('card_id');

        // If user has no preferences, return defaults
        if ($preferences->isEmpty()) {
            $defaults = UserDashboardPreference::getDefaultCards();
            return response()->json([
                'preferences' => $defaults,
                'cardNames' => UserDashboardPreference::getCardDisplayNames(),
            ]);
        }

        // Merge with defaults to ensure all cards are present
        $defaultCards = UserDashboardPreference::getDefaultCards();
        $mergedPreferences = [];

        foreach ($defaultCards as $default) {
            if ($preferences->has($default['card_id'])) {
                $pref = $preferences->get($default['card_id']);
                $mergedPreferences[] = [
                    'card_id' => $pref->card_id,
                    'order' => $pref->order,
                    'is_visible' => $pref->is_visible,
                    'size' => $pref->size ?? $default['size'],
                ];
            } else {
                $mergedPreferences[] = $default;
            }
        }

        // Sort by order
        usort($mergedPreferences, fn($a, $b) => $a['order'] <=> $b['order']);

        return response()->json([
            'preferences' => $mergedPreferences,
            'cardNames' => UserDashboardPreference::getCardDisplayNames(),
        ]);
    }

    /**
     * Update the user's dashboard preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Only premium users can customize their dashboard
        if (!$user->isPremium()) {
            return response()->json([
                'message' => 'Dashboard customization is a premium feature.',
            ], 403);
        }

        $userId = $user->id;

        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.card_id' => 'required|string|max:50',
            'preferences.*.order' => 'required|integer|min:0',
            'preferences.*.is_visible' => 'required|boolean',
            'preferences.*.size' => 'required|integer|in:3,4,6,8,9,12', // 12-column grid: 1/4, 1/3, 1/2, 2/3, 3/4, Full
        ]);

        // Validate that all card_ids are valid
        $validCardIds = array_column(UserDashboardPreference::getDefaultCards(), 'card_id');
        foreach ($validated['preferences'] as $pref) {
            if (!in_array($pref['card_id'], $validCardIds)) {
                return response()->json([
                    'message' => 'Invalid card_id: ' . $pref['card_id'],
                ], 422);
            }
        }

        // Update or create preferences
        foreach ($validated['preferences'] as $pref) {
            UserDashboardPreference::updateOrCreate(
                [
                    'user_id' => $userId,
                    'card_id' => $pref['card_id'],
                ],
                [
                    'order' => $pref['order'],
                    'is_visible' => $pref['is_visible'],
                    'size' => $pref['size'],
                ]
            );
        }

        // Clear dashboard cache for this user
        $this->clearDashboardCache($userId);

        return response()->json([
            'message' => 'Preferences updated successfully',
        ]);
    }

    /**
     * Reset the user's dashboard preferences to defaults.
     */
    public function reset(): JsonResponse
    {
        $userId = auth()->id();

        // Delete all user preferences
        UserDashboardPreference::where('user_id', $userId)->delete();

        // Clear dashboard cache
        $this->clearDashboardCache($userId);

        return response()->json([
            'message' => 'Preferences reset to defaults',
            'preferences' => UserDashboardPreference::getDefaultCards(),
            'cardNames' => UserDashboardPreference::getCardDisplayNames(),
        ]);
    }

    /**
     * Clear the dashboard cache for a user.
     */
    private function clearDashboardCache(int $userId): void
    {
        // Clear cache for all possible year filters
        $years = range(2020, (int) now()->year);
        foreach ($years as $year) {
            Cache::forget("dashboard_{$userId}_{$year}");
        }
        Cache::forget("dashboard_{$userId}_lifetime");
    }
}

