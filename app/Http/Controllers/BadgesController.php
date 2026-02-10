<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BadgesController extends Controller
{
    public function __construct(
        private BadgeService $badgeService
    ) {}

    /**
     * Display the badges page
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get all badges grouped by category
        $allBadges = Badge::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();
        
        // Get earned badge IDs for the user
        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();
        
        // Get user stats for progress calculation
        $stats = $this->badgeService->getUserStats($user);
        
        // Transform badges with earned status and progress
        $badgesWithProgress = $allBadges->map(function ($badge) use ($earnedBadgeIds, $stats, $user) {
            $isEarned = in_array($badge->id, $earnedBadgeIds);
            // Pass pre-computed stats to avoid redundant queries (198 badges = 198 queries otherwise!)
            $progress = $this->badgeService->getBadgeProgress($user, $badge, $stats);
            
            // Get earned_at if earned
            $earnedAt = null;
            if ($isEarned) {
                $pivot = $user->badges()->where('badges.id', $badge->id)->first()?->pivot;
                $earnedAt = $pivot?->earned_at;
            }
            
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'slug' => $badge->slug,
                'icon' => $badge->icon,
                'description' => $badge->description,
                'category' => $badge->category,
                'category_label' => $badge->category_label,
                'rarity' => $badge->rarity,
                'rarity_colors' => $badge->rarity_colors,
                'is_earned' => $isEarned,
                'earned_at' => $earnedAt,
                'progress' => $progress,
            ];
        });
        
        // Group by category
        $badgesByCategory = $badgesWithProgress->groupBy('category');
        
        // Calculate stats
        $totalBadges = $allBadges->count();
        $earnedCount = count($earnedBadgeIds);
        $completionPercentage = $totalBadges > 0 ? round(($earnedCount / $totalBadges) * 100) : 0;
        
        // Count by rarity
        $rarityStats = [
            'common' => ['total' => 0, 'earned' => 0],
            'uncommon' => ['total' => 0, 'earned' => 0],
            'rare' => ['total' => 0, 'earned' => 0],
            'epic' => ['total' => 0, 'earned' => 0],
            'legendary' => ['total' => 0, 'earned' => 0],
        ];
        
        foreach ($allBadges as $badge) {
            $rarityStats[$badge->rarity]['total']++;
            if (in_array($badge->id, $earnedBadgeIds)) {
                $rarityStats[$badge->rarity]['earned']++;
            }
        }
        
        return Inertia::render('Badges', [
            'badges' => $badgesWithProgress->values(),
            'badgesByCategory' => $badgesByCategory,
            'categories' => Badge::CATEGORIES,
            'stats' => [
                'total' => $totalBadges,
                'earned' => $earnedCount,
                'percentage' => $completionPercentage,
                'byRarity' => $rarityStats,
            ],
        ]);
    }

    /**
     * Get unnotified badges for the current user
     */
    public function getUnnotified(): JsonResponse
    {
        $user = auth()->user();
        
        $unnotifiedBadges = $user->badges()
            ->wherePivot('is_notified', false)
            ->get()
            ->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'icon' => $badge->icon,
                    'description' => $badge->description,
                    'rarity' => $badge->rarity,
                    'rarity_colors' => $badge->rarity_colors,
                ];
            });
        
        return response()->json($unnotifiedBadges);
    }

    /**
     * Mark badges as notified
     */
    public function markNotified(Request $request): JsonResponse
    {
        $user = auth()->user();
        $badgeIds = $request->input('badge_ids', []);
        
        if (!empty($badgeIds)) {
            $user->badges()
                ->wherePivotIn('badge_id', $badgeIds)
                ->update(['user_badges.is_notified' => true]);
        }
        
        return response()->json(['success' => true]);
    }
}

