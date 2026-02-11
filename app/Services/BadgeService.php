<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\FishingLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    /**
     * Check and award all eligible badges for a user
     */
    public function checkAndAwardBadges(User $user): array
    {
        $earnedBadges = [];
        $badges = Badge::active()->get();
        $stats = $this->getUserStats($user);

        foreach ($badges as $badge) {
            if (!$user->hasBadge($badge) && $this->checkBadgeRequirement($badge, $stats, $user)) {
                $this->awardBadge($user, $badge, $stats);
                $earnedBadges[] = $badge;
            }
        }

        return $earnedBadges;
    }

    /**
     * Check and revoke badges that the user no longer qualifies for
     */
    public function checkAndRevokeBadges(User $user): array
    {
        $revokedBadges = [];
        $stats = $this->getUserStats($user);

        // Get all badges the user currently has
        $earnedBadges = $user->badges()->get();

        foreach ($earnedBadges as $badge) {
            // Check if the user still meets the requirement
            if (!$this->checkBadgeRequirement($badge, $stats, $user)) {
                $this->revokeBadge($user, $badge);
                $revokedBadges[] = $badge;
            }
        }

        return $revokedBadges;
    }

    /**
     * Check badges and both award new ones and revoke ones no longer qualified for
     */
    public function syncBadges(User $user): array
    {
        $stats = $this->getUserStats($user);
        $badges = Badge::active()->get();
        $earnedBadges = [];
        $revokedBadges = [];

        // Check for new badges to award
        foreach ($badges as $badge) {
            if (!$user->hasBadge($badge) && $this->checkBadgeRequirement($badge, $stats, $user)) {
                $this->awardBadge($user, $badge, $stats);
                $earnedBadges[] = $badge;
            }
        }

        // Check for badges to revoke
        $userBadges = $user->badges()->get();
        foreach ($userBadges as $badge) {
            if (!$this->checkBadgeRequirement($badge, $stats, $user)) {
                $this->revokeBadge($user, $badge);
                $revokedBadges[] = $badge;
            }
        }

        return [
            'earned' => $earnedBadges,
            'revoked' => $revokedBadges,
        ];
    }

    /**
     * Award a badge to a user
     */
    public function awardBadge(User $user, Badge $badge, array $stats = []): void
    {
        $user->badges()->attach($badge->id, [
            'earned_at' => now(),
            'earned_data' => json_encode($stats),
            'is_notified' => false,
        ]);
    }

    /**
     * Revoke a badge from a user
     */
    public function revokeBadge(User $user, Badge $badge): void
    {
        $user->badges()->detach($badge->id);
    }

    /**
     * Get all user stats needed for badge checking
     */
    public function getUserStats(User $user): array
    {
        $userId = $user->id;
        $baseQuery = FishingLog::where('user_id', $userId);

        // === BASIC STATS ===
        $totalCaught = (clone $baseQuery)->sum('quantity');
        $logCount = (clone $baseQuery)->count();
        $maxSize = (clone $baseQuery)->max('max_size') ?? 0;
        $maxWeight = (clone $baseQuery)->max('max_weight') ?? 0;

        // === FRESHWATER STATS ===
        $freshwaterQuery = (clone $baseQuery)
            ->whereHas('fish', fn($q) => $q->whereRaw('LOWER(water_type) = ?', ['freshwater']));
        $freshwaterMaxSize = (clone $freshwaterQuery)->max('max_size') ?? 0;
        $freshwaterTrophyCount = (clone $freshwaterQuery)->where('max_size', '>=', 20)->count();
        $freshwaterOver30Count = (clone $freshwaterQuery)->where('max_size', '>=', 30)->count();
        $freshwaterOver40Count = (clone $freshwaterQuery)->where('max_size', '>=', 40)->count();
        $freshwaterMaxDailyTrophies = (clone $freshwaterQuery)
            ->where('max_size', '>=', 20)
            ->selectRaw('date, COUNT(*) as trophy_count')
            ->groupBy('date')
            ->orderByDesc('trophy_count')
            ->first();

        // === SALTWATER STATS ===
        $saltwaterQuery = (clone $baseQuery)
            ->whereHas('fish', fn($q) => $q->whereRaw('LOWER(water_type) = ?', ['saltwater']));
        $saltwaterMaxSize = (clone $saltwaterQuery)->max('max_size') ?? 0;
        $saltwaterTrophyCount = (clone $saltwaterQuery)->where('max_size', '>=', 30)->count(); // Saltwater trophies are 30"+
        $saltwaterOver40Count = (clone $saltwaterQuery)->where('max_size', '>=', 40)->count();
        $saltwaterOver50Count = (clone $saltwaterQuery)->where('max_size', '>=', 50)->count();
        $saltwaterOver60Count = (clone $saltwaterQuery)->where('max_size', '>=', 60)->count();
        $saltwaterMaxDailyTrophies = (clone $saltwaterQuery)
            ->where('max_size', '>=', 30)
            ->selectRaw('date, COUNT(*) as trophy_count')
            ->groupBy('date')
            ->orderByDesc('trophy_count')
            ->first();

        // Species count
        $speciesCount = (clone $baseQuery)
            ->whereNotNull('user_fish_id')
            ->distinct('user_fish_id')
            ->count('user_fish_id');

        // Location count
        $locationCount = (clone $baseQuery)
            ->whereNotNull('user_location_id')
            ->distinct('user_location_id')
            ->count('user_location_id');

        // Rod count
        $rodCount = (clone $baseQuery)
            ->whereNotNull('user_rod_id')
            ->distinct('user_rod_id')
            ->count('user_rod_id');

        // Fly count
        $flyCount = (clone $baseQuery)
            ->whereNotNull('user_fly_id')
            ->distinct('user_fly_id')
            ->count('user_fly_id');

        // === TIME-BASED STATS ===
        $earlyMorningCatches = (clone $baseQuery)
            ->whereNotNull('time')
            ->whereRaw('TIME(time) < ?', ['07:00:00'])
            ->sum('quantity');

        $nightCatches = (clone $baseQuery)
            ->whereNotNull('time')
            ->whereRaw('TIME(time) >= ?', ['20:00:00'])
            ->sum('quantity');

        $morningCatches = (clone $baseQuery)
            ->where('time_of_day', 'Morning')
            ->sum('quantity');

        $afternoonCatches = (clone $baseQuery)
            ->where('time_of_day', 'Afternoon')
            ->sum('quantity');

        $eveningCatches = (clone $baseQuery)
            ->where('time_of_day', 'Evening')
            ->sum('quantity');

        // === MOON PHASE STATS ===
        $fullMoonCatches = (clone $baseQuery)
            ->where('moon_phase', 'Full Moon')
            ->sum('quantity');

        $newMoonCatches = (clone $baseQuery)
            ->where('moon_phase', 'New Moon')
            ->sum('quantity');

        $moonAboveHorizonCatches = (clone $baseQuery)
            ->where('moon_position', 'Above Horizon')
            ->sum('quantity');

        $moonBelowHorizonCatches = (clone $baseQuery)
            ->where('moon_position', 'Below Horizon')
            ->sum('quantity');

        // Count distinct moon phases fished
        $moonPhasesFished = (clone $baseQuery)
            ->whereNotNull('moon_phase')
            ->distinct('moon_phase')
            ->count('moon_phase');

        // === SEASON STATS ===
        $springCatches = (clone $baseQuery)
            ->whereRaw('MONTH(date) IN (3, 4, 5)')
            ->sum('quantity');

        $summerCatches = (clone $baseQuery)
            ->whereRaw('MONTH(date) IN (6, 7, 8)')
            ->sum('quantity');

        $fallCatches = (clone $baseQuery)
            ->whereRaw('MONTH(date) IN (9, 10, 11)')
            ->sum('quantity');

        $winterCatches = (clone $baseQuery)
            ->whereRaw('MONTH(date) IN (12, 1, 2)')
            ->sum('quantity');

        $seasonsFished = $this->getSeasonsFished($userId);

        $monthsFished = (clone $baseQuery)
            ->selectRaw('DISTINCT MONTH(date) as month')
            ->pluck('month')
            ->count();

        // === DAILY STATS ===
        $dailyMax = (clone $baseQuery)
            ->select('date', DB::raw('SUM(quantity) as total'))
            ->groupBy('date')
            ->orderByDesc('total')
            ->first();

        // Daily species max
        $dailySpeciesMax = (clone $baseQuery)
            ->select('date', DB::raw('COUNT(DISTINCT user_fish_id) as species_count'))
            ->whereNotNull('user_fish_id')
            ->groupBy('date')
            ->orderByDesc('species_count')
            ->first();

        // === STREAK STATS ===
        $longestStreak = $this->calculateLongestStreak($userId);
        $catchStreak = $this->calculateCatchStreak($userId);
        $noSkunkStreak = $this->calculateNoSkunkStreak($userId);

        // === LOCATION STATS ===
        $locationMaxVisits = (clone $baseQuery)
            ->select('user_location_id', DB::raw('COUNT(*) as visits'))
            ->whereNotNull('user_location_id')
            ->groupBy('user_location_id')
            ->orderByDesc('visits')
            ->first();

        // === SPECIES STATS ===
        $speciesMaxCount = (clone $baseQuery)
            ->select('user_fish_id', DB::raw('SUM(quantity) as total'))
            ->whereNotNull('user_fish_id')
            ->groupBy('user_fish_id')
            ->orderByDesc('total')
            ->first();

        // === ROD/FLY STATS ===
        $rodMaxCatches = (clone $baseQuery)
            ->select('user_rod_id', DB::raw('SUM(quantity) as total'))
            ->whereNotNull('user_rod_id')
            ->groupBy('user_rod_id')
            ->orderByDesc('total')
            ->first();

        $flyMaxCatches = (clone $baseQuery)
            ->select('user_fly_id', DB::raw('SUM(quantity) as total'))
            ->whereNotNull('user_fly_id')
            ->groupBy('user_fly_id')
            ->orderByDesc('total')
            ->first();

        // === MISC STATS ===
        $accountAge = $user->created_at ? Carbon::parse($user->created_at)->diffInDays(now()) : 0;
        $notesCount = (clone $baseQuery)->whereNotNull('notes')->where('notes', '!=', '')->count();
        $skunkCount = (clone $baseQuery)->where('quantity', 0)->count();
        $weightLoggedCount = (clone $baseQuery)->whereNotNull('max_weight')->where('max_weight', '>', 0)->count();

        // Trophy count (fish over 20 inches)
        $trophyCount = (clone $baseQuery)->where('max_size', '>=', 20)->count();

        // Max daily trophies (most trophies caught in a single day)
        $maxDailyTrophies = (clone $baseQuery)
            ->where('max_size', '>=', 20)
            ->selectRaw('date, COUNT(*) as trophy_count')
            ->groupBy('date')
            ->orderByDesc('trophy_count')
            ->first();

        // Size categories
        $over30Count = (clone $baseQuery)->where('max_size', '>=', 30)->count();
        $over40Count = (clone $baseQuery)->where('max_size', '>=', 40)->count();

        return [
            // Basic stats
            'total_caught' => $totalCaught,
            'log_count' => $logCount,
            'max_size' => $maxSize,
            'max_weight' => $maxWeight,
            'species_count' => $speciesCount,
            'location_count' => $locationCount,
            'rod_count' => $rodCount,
            'fly_count' => $flyCount,
            'photo_count' => 0, // No photo column exists
            'daily_max' => $dailyMax?->total ?? 0,

            // Time-based
            'early_morning_catches' => $earlyMorningCatches,
            'night_catches' => $nightCatches,
            'morning_catches' => $morningCatches,
            'afternoon_catches' => $afternoonCatches,
            'evening_catches' => $eveningCatches,

            // Moon phase
            'full_moon_catches' => $fullMoonCatches,
            'new_moon_catches' => $newMoonCatches,
            'moon_above_horizon_catches' => $moonAboveHorizonCatches,
            'moon_below_horizon_catches' => $moonBelowHorizonCatches,
            'moon_phases_fished' => $moonPhasesFished,

            // Seasons
            'spring_catches' => $springCatches,
            'summer_catches' => $summerCatches,
            'fall_catches' => $fallCatches,
            'winter_catches' => $winterCatches,
            'seasons_fished' => $seasonsFished,
            'months_fished' => $monthsFished,

            // Daily
            'daily_species_max' => $dailySpeciesMax?->species_count ?? 0,

            // Streaks
            'longest_streak' => $longestStreak,
            'catch_streak' => $catchStreak,
            'no_skunk_streak' => $noSkunkStreak,

            // Location
            'location_max_visits' => $locationMaxVisits?->visits ?? 0,

            // Species
            'species_max_count' => $speciesMaxCount?->total ?? 0,

            // Rod/Fly
            'rod_max_catches' => $rodMaxCatches?->total ?? 0,
            'fly_max_catches' => $flyMaxCatches?->total ?? 0,

            // Misc
            'account_age' => $accountAge,
            'notes_count' => $notesCount,
            'skunk_count' => $skunkCount,
            'weight_logged_count' => $weightLoggedCount,
            'trophy_count' => $trophyCount,
            'max_daily_trophies' => $maxDailyTrophies?->trophy_count ?? 0,
            'over_30_count' => $over30Count,
            'over_40_count' => $over40Count,

            // Freshwater stats
            'freshwater_max_size' => $freshwaterMaxSize,
            'freshwater_trophy_count' => $freshwaterTrophyCount,
            'freshwater_over_30_count' => $freshwaterOver30Count,
            'freshwater_over_40_count' => $freshwaterOver40Count,
            'freshwater_max_daily_trophies' => $freshwaterMaxDailyTrophies?->trophy_count ?? 0,

            // Saltwater stats
            'saltwater_max_size' => $saltwaterMaxSize,
            'saltwater_trophy_count' => $saltwaterTrophyCount,
            'saltwater_over_40_count' => $saltwaterOver40Count,
            'saltwater_over_50_count' => $saltwaterOver50Count,
            'saltwater_over_60_count' => $saltwaterOver60Count,
            'saltwater_max_daily_trophies' => $saltwaterMaxDailyTrophies?->trophy_count ?? 0,
        ];
    }

    /**
     * Check if a badge requirement is met
     */
    protected function checkBadgeRequirement(Badge $badge, array $stats, User $user): bool
    {
        $field = $badge->requirement_field;
        $operator = $badge->requirement_operator;
        $value = $badge->requirement_value;

        // Map requirement fields to stats
        $statValue = $this->getStatValue($field, $stats, $user);

        if ($statValue === null) {
            return false;
        }

        return $this->compareValues($statValue, $operator, $value);
    }

    /**
     * Get the stat value for a requirement field
     */
    protected function getStatValue(string $field, array $stats, User $user): ?float
    {
        $mapping = [
            // Basic stats
            'total_caught' => 'total_caught',
            'species_count' => 'species_count',
            'location_count' => 'location_count',
            'max_size' => 'max_size',
            'max_weight' => 'max_weight',
            'log_count' => 'log_count',
            'rod_count' => 'rod_count',
            'fly_count' => 'fly_count',
            'photo_count' => 'photo_count',
            'quantity' => 'daily_max',
            'daily_quantity' => 'daily_max',

            // Time-based
            'time' => 'early_morning_catches', // For "early bird" type badges
            'early_start' => 'early_morning_catches',
            'late_fishing' => 'night_catches',

            // Moon phase
            'moon_phase' => 'full_moon_catches',
            'solunar_position' => 'moon_above_horizon_catches',

            // Seasons
            'season' => 'seasons_fished',
            'seasons_fished' => 'seasons_fished',
            'months_fished' => 'months_fished',

            // Streaks
            'fishing_streak' => 'longest_streak',
            'catch_streak' => 'catch_streak',
            'no_skunk_streak' => 'no_skunk_streak',

            // Location
            'location_max_visits' => 'location_max_visits',

            // Species
            'species_max_count' => 'species_max_count',
            'daily_species_count' => 'daily_species_max',
            'daily_species' => 'daily_species_max',

            // Rod/Fly
            'rod_max_catches' => 'rod_max_catches',
            'fly_max_catches' => 'fly_max_catches',

            // Misc
            'days_active' => 'account_age',
            'notes_count' => 'notes_count',
            'skunk_count' => 'skunk_count',
            'weight_logged' => 'weight_logged_count',
            'trophy_count' => 'trophy_count',
            'daily_trophies' => 'max_daily_trophies',

            // Freshwater
            'freshwater_max_size' => 'freshwater_max_size',
            'freshwater_trophy_count' => 'freshwater_trophy_count',
            'freshwater_over_30_count' => 'freshwater_over_30_count',
            'freshwater_over_40_count' => 'freshwater_over_40_count',
            'freshwater_daily_trophies' => 'freshwater_max_daily_trophies',

            // Saltwater
            'saltwater_max_size' => 'saltwater_max_size',
            'saltwater_trophy_count' => 'saltwater_trophy_count',
            'saltwater_over_40_count' => 'saltwater_over_40_count',
            'saltwater_over_50_count' => 'saltwater_over_50_count',
            'saltwater_over_60_count' => 'saltwater_over_60_count',
            'saltwater_daily_trophies' => 'saltwater_max_daily_trophies',
        ];

        if (isset($mapping[$field])) {
            return $stats[$mapping[$field]] ?? null;
        }

        return null;
    }

    /**
     * Compare values based on operator
     */
    protected function compareValues($actual, ?string $operator, $expected): bool
    {
        return match ($operator) {
            '>=' => $actual >= $expected,
            '>' => $actual > $expected,
            '<=' => $actual <= $expected,
            '<' => $actual < $expected,
            '=' => $actual == $expected,
            default => false,
        };
    }

    /**
     * Calculate the longest fishing streak (consecutive days fished)
     */
    protected function calculateLongestStreak(int $userId): int
    {
        $dates = FishingLog::where('user_id', $userId)
            ->select('date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        if (empty($dates)) {
            return 0;
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < count($dates); $i++) {
            $prevDate = Carbon::parse($dates[$i - 1]);
            $currDate = Carbon::parse($dates[$i]);

            if ($prevDate->addDay()->format('Y-m-d') === $currDate->format('Y-m-d')) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $longestStreak;
    }

    /**
     * Calculate the longest catch streak (consecutive days with fish caught)
     */
    protected function calculateCatchStreak(int $userId): int
    {
        $dates = FishingLog::where('user_id', $userId)
            ->where('quantity', '>', 0)
            ->select('date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        if (empty($dates)) {
            return 0;
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < count($dates); $i++) {
            $prevDate = Carbon::parse($dates[$i - 1]);
            $currDate = Carbon::parse($dates[$i]);

            if ($prevDate->addDay()->format('Y-m-d') === $currDate->format('Y-m-d')) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $longestStreak;
    }

    /**
     * Calculate the longest no-skunk streak (consecutive fishing days with catches)
     * This is similar to catch streak but specifically tracks days where quantity > 0
     */
    protected function calculateNoSkunkStreak(int $userId): int
    {
        // Get all fishing dates with their total quantity
        $dailyTotals = FishingLog::where('user_id', $userId)
            ->select('date', DB::raw('SUM(quantity) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->filter(fn($day) => $day->total > 0)
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->values()
            ->toArray();

        if (empty($dailyTotals)) {
            return 0;
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < count($dailyTotals); $i++) {
            $prevDate = Carbon::parse($dailyTotals[$i - 1]);
            $currDate = Carbon::parse($dailyTotals[$i]);

            if ($prevDate->addDay()->format('Y-m-d') === $currDate->format('Y-m-d')) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $longestStreak;
    }

    /**
     * Get the number of seasons fished
     */
    protected function getSeasonsFished(int $userId): int
    {
        $months = FishingLog::where('user_id', $userId)
            ->selectRaw('DISTINCT MONTH(date) as month')
            ->pluck('month')
            ->toArray();

        $seasons = [];
        foreach ($months as $month) {
            if (in_array($month, [3, 4, 5])) {
                $seasons['spring'] = true;
            } elseif (in_array($month, [6, 7, 8])) {
                $seasons['summer'] = true;
            } elseif (in_array($month, [9, 10, 11])) {
                $seasons['fall'] = true;
            } else {
                $seasons['winter'] = true;
            }
        }

        return count($seasons);
    }

    /**
     * Get earned badges for a user
     */
    public function getEarnedBadges(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->badges()->orderBy('pivot_earned_at', 'desc')->get();
    }

    /**
     * Get unearned badges for a user
     */
    public function getUnearnedBadges(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $earnedIds = $user->badges()->pluck('badges.id')->toArray();

        return Badge::active()
            ->whereNotIn('id', $earnedIds)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get badge progress for a user
     *
     * @param User $user The user to check progress for
     * @param Badge $badge The badge to check progress on
     * @param array|null $stats Pre-computed stats (optional, to avoid redundant queries)
     */
    public function getBadgeProgress(User $user, Badge $badge, ?array $stats = null): array
    {
        // Use provided stats or fetch them (for single badge lookups)
        $stats = $stats ?? $this->getUserStats($user);
        $field = $badge->requirement_field;
        $value = $badge->requirement_value;

        $current = $this->getStatValue($field, $stats, $user) ?? 0;
        $percentage = $value > 0 ? min(100, round(($current / $value) * 100)) : 0;

        return [
            'current' => $current,
            'required' => $value,
            'percentage' => $percentage,
            'earned' => $user->hasBadge($badge),
        ];
    }
}

