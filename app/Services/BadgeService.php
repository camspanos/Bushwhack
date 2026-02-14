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

        $waxingMoonCatches = (clone $baseQuery)
            ->whereIn('moon_phase', ['Waxing Crescent', 'First Quarter', 'Waxing Gibbous'])
            ->sum('quantity');

        $waningMoonCatches = (clone $baseQuery)
            ->whereIn('moon_phase', ['Waning Gibbous', 'Last Quarter', 'Waning Crescent'])
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
            'waxing_moon_catches' => $waxingMoonCatches,
            'waning_moon_catches' => $waningMoonCatches,
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
        $type = $badge->requirement_type;
        $field = $badge->requirement_field;
        $operator = $badge->requirement_operator;
        $value = $badge->requirement_value;
        $extra = $badge->requirement_extra ?? [];

        // Handle different requirement types
        return match ($type) {
            // Simple count/max comparisons
            'count', 'max', 'exists', 'daily_max', 'streak', 'catch_streak', 'no_skunk',
            'location_visits', 'species_max', 'rod_max', 'fly_max', 'account_age' => $this->checkSimpleRequirement($field, $operator, $value, $stats, $user),

            // Count with size filter (freshwater/saltwater)
            'count_where' => $this->checkCountWhereRequirement($field, $operator, $value, $extra, $stats),

            // Time-based badges
            'time_range', 'count_time', 'time_between', 'count_golden_hour', 'time_variety' => $this->checkTimeRequirement($type, $field, $operator, $value, $extra, $stats),
            'count_time_between' => $this->checkCountTimeBetweenRequirement($badge, $user),

            // Moon phase badges
            'moon_phase' => $this->checkMoonPhaseRequirement($extra, $stats),
            'count_moon' => $this->checkCountMoonRequirement($extra, $value, $stats),
            'moon_variety' => $this->checkMoonVarietyRequirement($value, $stats),
            'count_solunar' => $this->checkSolunarRequirement($operator, $value, $stats),
            'moon_position_variety' => $this->checkMoonPositionVarietyRequirement($value, $stats),
            'supermoon' => $this->checkSupermoonRequirement($user),

            // Season badges
            'season' => $this->checkSeasonRequirement($extra, $stats),
            'count_season' => $this->checkCountSeasonRequirement($extra, $value, $stats),
            'season_variety' => $this->checkSeasonVarietyRequirement($value, $stats),
            'month_variety' => $this->checkMonthVarietyRequirement($value, $stats),

            // Daily variety badges
            'daily_species' => $this->checkSimpleRequirement($field, $operator, $value, $stats, $user),

            // Weather badges
            'weather' => $this->checkWeatherRequirement($extra, $user),
            'count_weather' => $this->checkCountWeatherRequirement($extra, $value, $user),
            'wind' => $this->checkWindRequirement($extra, $user),
            'count_wind' => $this->checkCountWindRequirement($extra, $value, $user),
            'pressure' => $this->checkPressureRequirement($extra, $user),
            'count_pressure' => $this->checkCountPressureRequirement($extra, $value, $user),

            // Water condition badges
            'water_clarity' => $this->checkWaterClarityRequirement($extra, $user),
            'count_water_clarity' => $this->checkCountWaterClarityRequirement($extra, $value, $user),
            'water_level' => $this->checkWaterLevelRequirement($extra, $user),
            'count_water_level' => $this->checkCountWaterLevelRequirement($extra, $value, $user),
            'water_speed' => $this->checkWaterSpeedRequirement($extra, $user),
            'count_water_speed' => $this->checkCountWaterSpeedRequirement($extra, $value, $user),
            'tide_variety' => $this->checkTideVarietyRequirement($value, $user),
            'count_tide' => $this->checkCountTideRequirement($extra, $value, $user),
            'surface_condition' => $this->checkSurfaceConditionRequirement($extra, $user),
            'count_surface' => $this->checkCountSurfaceRequirement($extra, $value, $user),

            // Social badges
            'friend_trips' => $this->checkFriendTripsRequirement($value, $user),
            'party_boat' => $this->checkPartyBoatRequirement($value, $user),
            'solo_trips' => $this->checkSoloTripsRequirement($value, $user),
            'follower_count' => $this->checkFollowerCountRequirement($value, $user),
            'friend_variety' => $this->checkFriendVarietyRequirement($value, $user),
            'loyal_location' => $this->checkLoyalLocationRequirement($value, $user),
            'monthly_consistency' => $this->checkMonthlyConsistencyRequirement($value, $user),
            'weekly_consistency' => $this->checkWeeklyConsistencyRequirement($value, $user),

            // Special date badges
            'specific_date' => $this->checkSpecificDateRequirement($extra, $user),
            'holiday' => $this->checkHolidayRequirement($user),
            'birthday' => $this->checkBirthdayRequirement($user),

            // Location badges
            'new_spot_success' => $this->checkNewSpotSuccessRequirement($value, $user),

            // Rod/Fly type badges
            'count_rod_type' => false, // UserRod doesn't have type field
            'rod_variety' => $this->checkRodVarietyRequirement($value, $user),
            'count_fly_type' => $this->checkCountFlyTypeRequirement($extra, $value, $user),
            'fly_variety' => $this->checkFlyVarietyRequirement($value, $user),

            // Combo badges (multiple conditions)
            'combo' => $this->checkComboRequirement($extra, $stats, $user),

            // Challenge badges
            'challenge' => $this->checkChallengeRequirement($field, $value, $stats, $user),

            // Default: try simple mapping
            default => $this->checkSimpleRequirement($field, $operator, $value, $stats, $user),
        };
    }

    /**
     * Check simple requirement using field mapping
     */
    protected function checkSimpleRequirement(string $field, ?string $operator, $value, array $stats, User $user): bool
    {
        $statValue = $this->getStatValue($field, $stats, $user);
        if ($statValue === null) {
            return false;
        }
        return $this->compareValues($statValue, $operator, $value);
    }

    /**
     * Check count_where requirement (e.g., freshwater trophy count)
     */
    protected function checkCountWhereRequirement(string $field, ?string $operator, $value, array $extra, array $stats): bool
    {
        // These are already calculated in stats
        $statValue = $stats[$field] ?? null;
        if ($statValue === null) {
            return false;
        }
        return $this->compareValues($statValue, $operator, $value);
    }

    /**
     * Check time-based requirements
     */
    protected function checkTimeRequirement(string $type, string $field, ?string $operator, $value, array $extra, array $stats): bool
    {
        return match ($type) {
            'time_range' => ($stats['early_morning_catches'] ?? 0) >= 1 || ($stats['night_catches'] ?? 0) >= 1,
            'count_time' => match ($field) {
                'early_start' => ($stats['early_morning_catches'] ?? 0) >= $value,
                'late_fishing' => ($stats['night_catches'] ?? 0) >= $value,
                default => false,
            },
            'time_between' => true, // Lunch break - any catch between 11am-1pm (simplified)
            'count_golden_hour' => ($stats['morning_catches'] ?? 0) + ($stats['evening_catches'] ?? 0) >= $value,
            'time_variety' => ($stats['morning_catches'] ?? 0) > 0 && ($stats['afternoon_catches'] ?? 0) > 0 && ($stats['evening_catches'] ?? 0) > 0,
            default => false,
        };
    }

    /**
     * Check moon phase requirement (caught fish during specific phase)
     */
    protected function checkMoonPhaseRequirement(array $extra, array $stats): bool
    {
        $phase = $extra['phase'] ?? null;
        return match ($phase) {
            'full' => ($stats['full_moon_catches'] ?? 0) >= 1,
            'new' => ($stats['new_moon_catches'] ?? 0) >= 1,
            'waxing' => ($stats['waxing_moon_catches'] ?? 0) >= 1,
            'waning' => ($stats['waning_moon_catches'] ?? 0) >= 1,
            default => false,
        };
    }

    /**
     * Check count moon requirement (X catches during specific phase)
     */
    protected function checkCountMoonRequirement(array $extra, $value, array $stats): bool
    {
        $phase = $extra['phase'] ?? null;
        return match ($phase) {
            'full' => ($stats['full_moon_catches'] ?? 0) >= $value,
            'new' => ($stats['new_moon_catches'] ?? 0) >= $value,
            'waxing' => ($stats['waxing_moon_catches'] ?? 0) >= $value,
            'waning' => ($stats['waning_moon_catches'] ?? 0) >= $value,
            default => false,
        };
    }

    /**
     * Check moon variety requirement (fished during X different phases)
     */
    protected function checkMoonVarietyRequirement($value, array $stats): bool
    {
        return ($stats['moon_phases_fished'] ?? 0) >= $value;
    }

    /**
     * Check solunar requirement
     */
    protected function checkSolunarRequirement(?string $operator, $value, array $stats): bool
    {
        // Major solunar periods = moon above horizon
        if ($operator === 'major') {
            return ($stats['moon_above_horizon_catches'] ?? 0) >= $value;
        }
        return false;
    }

    /**
     * Check moon position variety requirement
     */
    protected function checkMoonPositionVarietyRequirement($value, array $stats): bool
    {
        $positions = 0;
        if (($stats['moon_above_horizon_catches'] ?? 0) > 0) $positions++;
        if (($stats['moon_below_horizon_catches'] ?? 0) > 0) $positions++;
        // Add more positions if tracked
        return $positions >= min($value, 2);
    }

    /**
     * Check season requirement (caught fish in specific season)
     */
    protected function checkSeasonRequirement(array $extra, array $stats): bool
    {
        $season = $extra['season'] ?? null;
        return match ($season) {
            'spring' => ($stats['spring_catches'] ?? 0) >= 1,
            'summer' => ($stats['summer_catches'] ?? 0) >= 1,
            'fall' => ($stats['fall_catches'] ?? 0) >= 1,
            'winter' => ($stats['winter_catches'] ?? 0) >= 1,
            default => false,
        };
    }

    /**
     * Check count season requirement (X catches in specific season)
     */
    protected function checkCountSeasonRequirement(array $extra, $value, array $stats): bool
    {
        $season = $extra['season'] ?? null;
        return match ($season) {
            'spring' => ($stats['spring_catches'] ?? 0) >= $value,
            'summer' => ($stats['summer_catches'] ?? 0) >= $value,
            'fall' => ($stats['fall_catches'] ?? 0) >= $value,
            'winter' => ($stats['winter_catches'] ?? 0) >= $value,
            default => false,
        };
    }

    /**
     * Check season variety requirement (fished in X different seasons)
     */
    protected function checkSeasonVarietyRequirement($value, array $stats): bool
    {
        return ($stats['seasons_fished'] ?? 0) >= $value;
    }

    /**
     * Check month variety requirement (fished in X different months)
     */
    protected function checkMonthVarietyRequirement($value, array $stats): bool
    {
        return ($stats['months_fished'] ?? 0) >= $value;
    }

    /**
     * Check weather requirement (caught fish in specific weather)
     */
    protected function checkWeatherRequirement(array $extra, User $user): bool
    {
        $weatherType = $extra['type'] ?? null;
        if (!$weatherType) return false;

        return FishingLog::where('user_id', $user->id)
            ->whereHas('weather', function ($q) use ($weatherType) {
                $this->applyWeatherCondition($q, $weatherType);
            })
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Check count weather requirement (X catches in specific weather)
     */
    protected function checkCountWeatherRequirement(array $extra, $value, User $user): bool
    {
        $weatherType = $extra['type'] ?? null;
        if (!$weatherType) return false;

        $count = FishingLog::where('user_id', $user->id)
            ->whereHas('weather', function ($q) use ($weatherType) {
                $this->applyWeatherCondition($q, $weatherType);
            })
            ->sum('quantity');

        return $count >= $value;
    }

    /**
     * Apply weather condition to query
     */
    protected function applyWeatherCondition($query, string $weatherType): void
    {
        match ($weatherType) {
            'rain' => $query->whereIn('precipitation', ['light rain', 'rain', 'heavy rain', 'drizzle']),
            'snow' => $query->whereIn('precipitation', ['snow', 'light snow', 'heavy snow', 'sleet']),
            'sunny' => $query->whereIn('cloud', ['clear', 'sunny', 'mostly sunny']),
            'cloudy' => $query->whereIn('cloud', ['cloudy', 'overcast', 'mostly cloudy']),
            'storm' => $query->whereIn('precipitation', ['thunderstorm', 'storm', 'heavy rain']),
            default => $query->whereRaw('1=0'), // No match
        };
    }

    /**
     * Check wind requirement
     */
    protected function checkWindRequirement(array $extra, User $user): bool
    {
        $windType = $extra['type'] ?? null;
        if (!$windType) return false;

        return FishingLog::where('user_id', $user->id)
            ->whereHas('weather', function ($q) use ($windType) {
                $this->applyWindCondition($q, $windType);
            })
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Check count wind requirement
     */
    protected function checkCountWindRequirement(array $extra, $value, User $user): bool
    {
        $windType = $extra['type'] ?? null;
        if (!$windType) return false;

        $count = FishingLog::where('user_id', $user->id)
            ->whereHas('weather', function ($q) use ($windType) {
                $this->applyWindCondition($q, $windType);
            })
            ->sum('quantity');

        return $count >= $value;
    }

    /**
     * Apply wind condition to query
     */
    protected function applyWindCondition($query, string $windType): void
    {
        match ($windType) {
            'calm' => $query->whereIn('wind', ['calm', 'light', 'none']),
            'windy' => $query->whereIn('wind', ['moderate', 'strong', 'gusty']),
            'gale' => $query->whereIn('wind', ['strong', 'gale', 'very strong']),
            default => $query->whereRaw('1=0'),
        };
    }

    /**
     * Check pressure requirement
     */
    protected function checkPressureRequirement(array $extra, User $user): bool
    {
        $pressureType = $extra['type'] ?? null;
        if (!$pressureType) return false;

        return FishingLog::where('user_id', $user->id)
            ->whereHas('weather', function ($q) use ($pressureType) {
                $this->applyPressureCondition($q, $pressureType);
            })
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Check count pressure requirement
     */
    protected function checkCountPressureRequirement(array $extra, $value, User $user): bool
    {
        $pressureType = $extra['type'] ?? null;
        if (!$pressureType) return false;

        $count = FishingLog::where('user_id', $user->id)
            ->whereHas('weather', function ($q) use ($pressureType) {
                $this->applyPressureCondition($q, $pressureType);
            })
            ->sum('quantity');

        return $count >= $value;
    }

    /**
     * Apply pressure condition to query
     */
    protected function applyPressureCondition($query, string $pressureType): void
    {
        match ($pressureType) {
            'low' => $query->whereIn('barometric_pressure', ['low', 'falling', 'very low']),
            'high' => $query->whereIn('barometric_pressure', ['high', 'rising', 'very high']),
            'stable' => $query->whereIn('barometric_pressure', ['stable', 'steady', 'normal']),
            default => $query->whereRaw('1=0'),
        };
    }

    /**
     * Check specific date requirement (e.g., New Year's Day, July 4th)
     */
    protected function checkSpecificDateRequirement(array $extra, User $user): bool
    {
        $day = $extra['day'] ?? null;
        $month = $extra['month'] ?? null;
        if (!$day || !$month) return false;

        return FishingLog::where('user_id', $user->id)
            ->whereRaw('DAY(date) = ?', [$day])
            ->whereRaw('MONTH(date) = ?', [$month])
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Check holiday requirement (any major holiday)
     */
    protected function checkHolidayRequirement(User $user): bool
    {
        // Check common US holidays
        $holidays = [
            ['month' => 1, 'day' => 1],   // New Year's Day
            ['month' => 7, 'day' => 4],   // Independence Day
            ['month' => 12, 'day' => 25], // Christmas
            ['month' => 11, 'day' => 11], // Veterans Day
            ['month' => 5, 'day' => 31],  // Memorial Day (approximate)
            ['month' => 9, 'day' => 1],   // Labor Day (approximate)
        ];

        foreach ($holidays as $holiday) {
            $exists = FishingLog::where('user_id', $user->id)
                ->whereRaw('DAY(date) = ?', [$holiday['day']])
                ->whereRaw('MONTH(date) = ?', [$holiday['month']])
                ->where('quantity', '>', 0)
                ->exists();
            if ($exists) return true;
        }

        return false;
    }

    /**
     * Check birthday requirement (caught fish on user's birthday)
     */
    protected function checkBirthdayRequirement(User $user): bool
    {
        if (!$user->birthday) {
            return false;
        }

        $day = $user->birthday->day;
        $month = $user->birthday->month;

        return FishingLog::where('user_id', $user->id)
            ->whereRaw('DAY(date) = ?', [$day])
            ->whereRaw('MONTH(date) = ?', [$month])
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Check new spot success requirement
     */
    protected function checkNewSpotSuccessRequirement($value, User $user): bool
    {
        // Count locations where user caught fish on their first visit
        $locations = FishingLog::where('user_id', $user->id)
            ->whereNotNull('user_location_id')
            ->select('user_location_id')
            ->selectRaw('MIN(date) as first_visit')
            ->selectRaw('SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as success_count')
            ->groupBy('user_location_id')
            ->get();

        $newSpotSuccesses = 0;
        foreach ($locations as $loc) {
            // Check if first visit was successful
            $firstVisitSuccess = FishingLog::where('user_id', $user->id)
                ->where('user_location_id', $loc->user_location_id)
                ->where('date', $loc->first_visit)
                ->where('quantity', '>', 0)
                ->exists();
            if ($firstVisitSuccess) $newSpotSuccesses++;
        }

        return $newSpotSuccesses >= $value;
    }

    /**
     * Check rod variety requirement
     */
    protected function checkRodVarietyRequirement($value, User $user): bool
    {
        // Count distinct rods used in a single day
        $maxRodsInDay = FishingLog::where('user_id', $user->id)
            ->whereNotNull('user_rod_id')
            ->select('date')
            ->selectRaw('COUNT(DISTINCT user_rod_id) as rod_count')
            ->groupBy('date')
            ->orderByDesc('rod_count')
            ->first();

        return ($maxRodsInDay?->rod_count ?? 0) >= $value;
    }

    /**
     * Check count fly type requirement
     */
    protected function checkCountFlyTypeRequirement(array $extra, $value, User $user): bool
    {
        $flyType = $extra['type'] ?? null;
        if (!$flyType) return false;

        $count = FishingLog::where('user_id', $user->id)
            ->whereHas('fly', function ($q) use ($flyType) {
                $q->where('type', $flyType);
            })
            ->sum('quantity');

        return $count >= $value;
    }

    /**
     * Check fly variety requirement
     */
    protected function checkFlyVarietyRequirement($value, User $user): bool
    {
        // Count distinct flies used in a single day
        $maxFliesInDay = FishingLog::where('user_id', $user->id)
            ->whereNotNull('user_fly_id')
            ->select('date')
            ->selectRaw('COUNT(DISTINCT user_fly_id) as fly_count')
            ->groupBy('date')
            ->orderByDesc('fly_count')
            ->first();

        return ($maxFliesInDay?->fly_count ?? 0) >= $value;
    }

    /**
     * Check combo requirement (multiple conditions)
     */
    protected function checkComboRequirement(array $extra, array $stats, User $user): bool
    {
        $conditions = $extra['conditions'] ?? [];
        if (empty($conditions)) return false;

        // All conditions must be met in a single log entry
        $query = FishingLog::where('user_id', $user->id)->where('quantity', '>', 0);

        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? null;
            $condValue = $condition['value'] ?? null;

            match ($type) {
                'time_of_day' => $query->where('time_of_day', $condValue),
                'moon_phase' => $query->where('moon_phase', $condValue),
                'season' => $query->whereRaw($this->getSeasonCondition($condValue)),
                'size_min' => $query->where('max_size', '>=', $condValue),
                'quantity_min' => $query->where('quantity', '>=', $condValue),
                'weather' => $query->whereHas('weather', fn($q) => $this->applyWeatherCondition($q, $condValue)),
                'new_location' => null, // Complex - skip for now
                default => null,
            };
        }

        return $query->exists();
    }

    /**
     * Get SQL condition for season
     */
    protected function getSeasonCondition(string $season): string
    {
        return match ($season) {
            'spring' => 'MONTH(date) IN (3, 4, 5)',
            'summer' => 'MONTH(date) IN (6, 7, 8)',
            'fall' => 'MONTH(date) IN (9, 10, 11)',
            'winter' => 'MONTH(date) IN (12, 1, 2)',
            default => '1=0',
        };
    }

    /**
     * Check challenge requirement
     */
    protected function checkChallengeRequirement(string $field, $value, array $stats, User $user): bool
    {
        return match ($field) {
            // Time challenges
            'early_start' => ($stats['early_morning_catches'] ?? 0) >= $value,
            'late_fishing' => ($stats['night_catches'] ?? 0) >= $value,

            // Species challenges
            'daily_species_count' => ($stats['daily_species_max'] ?? 0) >= $value,

            // Quantity challenges
            'daily_quantity' => ($stats['daily_max'] ?? 0) >= $value,

            // Trophy challenges
            'freshwater_daily_trophies' => ($stats['freshwater_max_daily_trophies'] ?? 0) >= $value,
            'saltwater_daily_trophies' => ($stats['saltwater_max_daily_trophies'] ?? 0) >= $value,

            // Skunk challenges
            'skunk_count' => ($stats['skunk_count'] ?? 0) >= $value,

            // Notes challenges
            'notes_count' => ($stats['notes_count'] ?? 0) >= $value,

            // Minimalist/single item challenges
            'single_fly_catches' => $this->checkSingleItemCatches($user, 'user_fly_id', $value),
            'single_rod_catches' => $this->checkSingleItemCatches($user, 'user_rod_id', $value),
            'single_location_catches' => $this->checkSingleItemCatches($user, 'user_location_id', $value),

            // Streak challenges
            'weekly_catch_streak' => $this->checkWeeklyStreak($user, $value),
            'monthly_catch_streak' => $this->checkMonthlyStreak($user, $value),
            'consecutive_months' => $this->checkConsecutiveMonths($user, $value),
            'weekend_streak' => $this->checkWeekendStreak($user, $value),
            'weekday_streak' => $this->checkWeekdayStreak($user, $value),

            // Daily variety challenges
            'daily_locations' => $this->checkDailyVariety($user, 'user_location_id', $value),
            'daily_flies' => $this->checkDailyVariety($user, 'user_fly_id', $value),
            'daily_rods' => $this->checkDailyVariety($user, 'user_rod_id', $value),

            // Species focus
            'monthly_species_max' => $this->checkMonthlySpeciesMax($user, $value),

            // PB count
            'pb_count' => $this->checkPBCount($user, $value),

            // Full day fishing
            'full_day_fishing' => $this->checkFullDayFishing($user),
            'daily_hours' => $this->checkDailyHours($user, $value),

            // Comeback challenges
            'comeback_after_skunk' => $this->checkComebackAfterSkunk($user),
            'trophy_after_skunk' => $this->checkTrophyAfterSkunk($user),

            // Size variety
            'daily_size_range' => $this->checkDailySizeRange($user, $value),

            // Time to catch
            'hours_before_catch' => false, // Need start_time tracking
            'minutes_to_catch' => false, // Need start_time tracking

            // Release count
            'released_count' => false, // No released field in FishingLog

            // Condition tracking
            'water_temp_count' => $this->checkConditionCount($user, 'waterCondition', 'temperature', $value),
            'air_temp_count' => $this->checkConditionCount($user, 'weather', 'temperature', $value),
            'weather_count' => $this->checkWeatherLogCount($user, $value),
            'clarity_count' => $this->checkConditionCount($user, 'waterCondition', 'clarity', $value),
            'flow_count' => $this->checkConditionCount($user, 'waterCondition', 'speed', $value),

            default => false,
        };
    }

    /**
     * Check single item catches (e.g., 50 catches with same fly)
     */
    protected function checkSingleItemCatches(User $user, string $field, $value): bool
    {
        $maxCatches = FishingLog::where('user_id', $user->id)
            ->whereNotNull($field)
            ->select($field)
            ->selectRaw('SUM(quantity) as total')
            ->groupBy($field)
            ->orderByDesc('total')
            ->first();

        return ($maxCatches?->total ?? 0) >= $value;
    }

    /**
     * Check weekly catch streak
     */
    protected function checkWeeklyStreak(User $user, $value): bool
    {
        // Check if user caught fish every day for a week
        return ($this->calculateCatchStreak($user->id)) >= 7 * $value;
    }

    /**
     * Check monthly catch streak
     */
    protected function checkMonthlyStreak(User $user, $value): bool
    {
        return ($this->calculateCatchStreak($user->id)) >= 30 * $value;
    }

    /**
     * Check consecutive months fished
     */
    protected function checkConsecutiveMonths(User $user, $value): bool
    {
        $months = FishingLog::where('user_id', $user->id)
            ->selectRaw('DISTINCT YEAR(date) as year, MONTH(date) as month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        if ($months->isEmpty()) return false;

        $maxStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < $months->count(); $i++) {
            $prev = $months[$i - 1];
            $curr = $months[$i];

            $prevDate = Carbon::createFromDate($prev->year, $prev->month, 1);
            $currDate = Carbon::createFromDate($curr->year, $curr->month, 1);

            if ($prevDate->addMonth()->equalTo($currDate)) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $maxStreak >= $value;
    }

    /**
     * Check weekend streak
     */
    protected function checkWeekendStreak(User $user, $value): bool
    {
        $weekendDays = FishingLog::where('user_id', $user->id)
            ->whereRaw('DAYOFWEEK(date) IN (1, 7)') // Sunday = 1, Saturday = 7
            ->where('quantity', '>', 0)
            ->select('date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date');

        if ($weekendDays->isEmpty()) return false;

        // Count consecutive weekends
        $maxStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < $weekendDays->count(); $i++) {
            $prev = Carbon::parse($weekendDays[$i - 1]);
            $curr = Carbon::parse($weekendDays[$i]);

            // If within same weekend or next weekend
            if ($curr->diffInDays($prev) <= 8) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $maxStreak >= $value;
    }

    /**
     * Check weekday streak
     */
    protected function checkWeekdayStreak(User $user, $value): bool
    {
        $weekdayDays = FishingLog::where('user_id', $user->id)
            ->whereRaw('DAYOFWEEK(date) BETWEEN 2 AND 6') // Monday-Friday
            ->where('quantity', '>', 0)
            ->select('date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date');

        return $weekdayDays->count() >= $value;
    }

    /**
     * Check daily variety (e.g., 3 different locations in one day)
     */
    protected function checkDailyVariety(User $user, string $field, $value): bool
    {
        $maxVariety = FishingLog::where('user_id', $user->id)
            ->whereNotNull($field)
            ->select('date')
            ->selectRaw("COUNT(DISTINCT {$field}) as variety")
            ->groupBy('date')
            ->orderByDesc('variety')
            ->first();

        return ($maxVariety?->variety ?? 0) >= $value;
    }

    /**
     * Check monthly species max
     */
    protected function checkMonthlySpeciesMax(User $user, $value): bool
    {
        $maxSpecies = FishingLog::where('user_id', $user->id)
            ->whereNotNull('user_fish_id')
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(DISTINCT user_fish_id) as species_count')
            ->groupByRaw('YEAR(date), MONTH(date)')
            ->orderByDesc('species_count')
            ->first();

        return ($maxSpecies?->species_count ?? 0) >= $value;
    }

    /**
     * Check PB (personal best) count
     */
    protected function checkPBCount(User $user, $value): bool
    {
        // Count how many times user beat their previous max size
        $logs = FishingLog::where('user_id', $user->id)
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->orderBy('date')
            ->orderBy('created_at')
            ->get(['max_size']);

        $pbCount = 0;
        $currentMax = 0;

        foreach ($logs as $log) {
            if ($log->max_size > $currentMax) {
                $pbCount++;
                $currentMax = $log->max_size;
            }
        }

        return $pbCount >= $value;
    }

    /**
     * Check full day fishing
     */
    protected function checkFullDayFishing(User $user): bool
    {
        // Check if user has logs in morning, afternoon, and evening on same day
        $fullDays = FishingLog::where('user_id', $user->id)
            ->select('date')
            ->selectRaw("SUM(CASE WHEN time_of_day = 'Morning' THEN 1 ELSE 0 END) as morning")
            ->selectRaw("SUM(CASE WHEN time_of_day = 'Afternoon' THEN 1 ELSE 0 END) as afternoon")
            ->selectRaw("SUM(CASE WHEN time_of_day = 'Evening' THEN 1 ELSE 0 END) as evening")
            ->groupBy('date')
            ->havingRaw('morning > 0 AND afternoon > 0 AND evening > 0')
            ->exists();

        return $fullDays;
    }

    /**
     * Check daily hours (simplified - check for multiple time_of_day entries)
     */
    protected function checkDailyHours(User $user, $value): bool
    {
        // Approximate: 3 time periods = ~8+ hours
        return $this->checkFullDayFishing($user);
    }

    /**
     * Check comeback after skunk
     */
    protected function checkComebackAfterSkunk(User $user): bool
    {
        $logs = FishingLog::where('user_id', $user->id)
            ->orderBy('date')
            ->get(['date', 'quantity']);

        for ($i = 1; $i < $logs->count(); $i++) {
            if ($logs[$i - 1]->quantity == 0 && $logs[$i]->quantity > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check trophy after skunk
     */
    protected function checkTrophyAfterSkunk(User $user): bool
    {
        $logs = FishingLog::where('user_id', $user->id)
            ->orderBy('date')
            ->get(['date', 'quantity', 'max_size']);

        for ($i = 1; $i < $logs->count(); $i++) {
            if ($logs[$i - 1]->quantity == 0 && $logs[$i]->max_size >= 20) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check daily size range
     */
    protected function checkDailySizeRange(User $user, $value): bool
    {
        $sizeRange = FishingLog::where('user_id', $user->id)
            ->whereNotNull('max_size')
            ->where('max_size', '>', 0)
            ->select('date')
            ->selectRaw('MAX(max_size) - MIN(max_size) as size_range')
            ->groupBy('date')
            ->orderByDesc('size_range')
            ->first();

        return ($sizeRange?->size_range ?? 0) >= $value;
    }

    /**
     * Check condition count (logs with specific condition recorded)
     */
    protected function checkConditionCount(User $user, string $relation, string $field, $value): bool
    {
        $count = FishingLog::where('user_id', $user->id)
            ->whereHas($relation, function ($q) use ($field) {
                $q->whereNotNull($field)->where($field, '!=', '');
            })
            ->count();

        return $count >= $value;
    }

    /**
     * Check weather log count
     */
    protected function checkWeatherLogCount(User $user, $value): bool
    {
        $count = FishingLog::where('user_id', $user->id)
            ->whereNotNull('user_weather_id')
            ->count();

        return $count >= $value;
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
     * Check count_time_between requirement (e.g., catches between midnight and 4am)
     */
    protected function checkCountTimeBetweenRequirement(Badge $badge, User $user): bool
    {
        $startHour = $badge->requirement_value;
        $endHour = $badge->requirement_value2 ?? ($startHour + 1);
        $requiredCount = $badge->requirement_extra['count'] ?? 1;

        $count = FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereNotNull('time')
            ->whereRaw('HOUR(time) >= ? AND HOUR(time) < ?', [$startHour, $endHour])
            ->sum('quantity');

        return $count >= $requiredCount;
    }

    /**
     * Check supermoon requirement
     * Supermoons occur when the moon is full and at its closest point to Earth
     * We'll check for full moon with high moon_altitude (above 80%)
     */
    protected function checkSupermoonRequirement(User $user): bool
    {
        // Check for full moon catches with high altitude (approximating supermoon)
        return FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->where('moon_phase', 'Full Moon')
            ->where('moon_altitude', '>=', 80)
            ->exists();
    }

    /**
     * Check water clarity requirement
     */
    protected function checkWaterClarityRequirement(array $extra, User $user): bool
    {
        $clarityValues = $extra['clarity'] ?? [];
        if (!is_array($clarityValues)) {
            $clarityValues = [$clarityValues];
        }

        return FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($clarityValues) {
                $query->whereIn('clarity', $clarityValues);
            })
            ->exists();
    }

    /**
     * Check count water clarity requirement
     */
    protected function checkCountWaterClarityRequirement(array $extra, int $requiredCount, User $user): bool
    {
        $clarityValues = $extra['clarity'] ?? [];
        if (!is_array($clarityValues)) {
            $clarityValues = [$clarityValues];
        }

        $count = FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($clarityValues) {
                $query->whereIn('clarity', $clarityValues);
            })
            ->sum('quantity');

        return $count >= $requiredCount;
    }

    /**
     * Check water level requirement
     */
    protected function checkWaterLevelRequirement(array $extra, User $user): bool
    {
        $levelValues = $extra['level'] ?? [];
        if (!is_array($levelValues)) {
            $levelValues = [$levelValues];
        }

        return FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($levelValues) {
                $query->whereIn('level', $levelValues);
            })
            ->exists();
    }

    /**
     * Check count water level requirement
     */
    protected function checkCountWaterLevelRequirement(array $extra, int $requiredCount, User $user): bool
    {
        $levelValues = $extra['level'] ?? [];
        if (!is_array($levelValues)) {
            $levelValues = [$levelValues];
        }

        $count = FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($levelValues) {
                $query->whereIn('level', $levelValues);
            })
            ->sum('quantity');

        return $count >= $requiredCount;
    }

    /**
     * Check water speed requirement
     */
    protected function checkWaterSpeedRequirement(array $extra, User $user): bool
    {
        $speedValues = $extra['speed'] ?? [];
        if (!is_array($speedValues)) {
            $speedValues = [$speedValues];
        }

        return FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($speedValues) {
                $query->whereIn('speed', $speedValues);
            })
            ->exists();
    }

    /**
     * Check count water speed requirement
     */
    protected function checkCountWaterSpeedRequirement(array $extra, int $requiredCount, User $user): bool
    {
        $speedValues = $extra['speed'] ?? [];
        if (!is_array($speedValues)) {
            $speedValues = [$speedValues];
        }

        $count = FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($speedValues) {
                $query->whereIn('speed', $speedValues);
            })
            ->sum('quantity');

        return $count >= $requiredCount;
    }

    /**
     * Check tide variety requirement (fished during all tide phases)
     */
    protected function checkTideVarietyRequirement(int $requiredCount, User $user): bool
    {
        $tidePhases = FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) {
                $query->whereNotNull('tide');
            })
            ->with('waterCondition')
            ->get()
            ->pluck('waterCondition.tide')
            ->unique()
            ->filter()
            ->count();

        return $tidePhases >= $requiredCount;
    }

    /**
     * Check count tide requirement
     */
    protected function checkCountTideRequirement(array $extra, int $requiredCount, User $user): bool
    {
        $tideValue = $extra['tide'] ?? null;
        if (!$tideValue) {
            return false;
        }

        $count = FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($tideValue) {
                $query->where('tide', $tideValue);
            })
            ->sum('quantity');

        return $count >= $requiredCount;
    }

    /**
     * Check surface condition requirement
     */
    protected function checkSurfaceConditionRequirement(array $extra, User $user): bool
    {
        $surfaceValues = $extra['surface'] ?? [];
        if (!is_array($surfaceValues)) {
            $surfaceValues = [$surfaceValues];
        }

        return FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($surfaceValues) {
                $query->whereIn('surface_condition', $surfaceValues);
            })
            ->exists();
    }

    /**
     * Check count surface condition requirement
     */
    protected function checkCountSurfaceRequirement(array $extra, int $requiredCount, User $user): bool
    {
        $surfaceValues = $extra['surface'] ?? [];
        if (!is_array($surfaceValues)) {
            $surfaceValues = [$surfaceValues];
        }

        $count = FishingLog::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->whereHas('waterCondition', function ($query) use ($surfaceValues) {
                $query->whereIn('surface_condition', $surfaceValues);
            })
            ->sum('quantity');

        return $count >= $requiredCount;
    }

    /**
     * Check friend trips requirement (fishing with friends)
     */
    protected function checkFriendTripsRequirement(int $requiredCount, User $user): bool
    {
        // Count distinct dates where user fished with at least one friend
        $count = FishingLog::where('user_id', $user->id)
            ->whereHas('friends')
            ->distinct('date')
            ->count('date');

        return $count >= $requiredCount;
    }

    /**
     * Check party boat requirement (fishing with multiple friends on one trip)
     */
    protected function checkPartyBoatRequirement(int $requiredFriends, User $user): bool
    {
        // Find the max number of friends on any single fishing log
        $maxFriends = FishingLog::where('user_id', $user->id)
            ->withCount('friends')
            ->orderByDesc('friends_count')
            ->first();

        return $maxFriends && $maxFriends->friends_count >= $requiredFriends;
    }

    /**
     * Check solo trips requirement (fishing without friends)
     */
    protected function checkSoloTripsRequirement(int $requiredCount, User $user): bool
    {
        // Count distinct dates where user fished without any friends
        $count = FishingLog::where('user_id', $user->id)
            ->whereDoesntHave('friends')
            ->distinct('date')
            ->count('date');

        return $count >= $requiredCount;
    }

    /**
     * Check follower count requirement
     */
    protected function checkFollowerCountRequirement(int $requiredCount, User $user): bool
    {
        return $user->followers()->count() >= $requiredCount;
    }

    /**
     * Check friend variety requirement (fished with different friends)
     */
    protected function checkFriendVarietyRequirement(int $requiredCount, User $user): bool
    {
        $uniqueFriends = \DB::table('fishing_log_user_friend')
            ->join('fishing_logs', 'fishing_log_user_friend.fishing_log_id', '=', 'fishing_logs.id')
            ->where('fishing_logs.user_id', $user->id)
            ->distinct('fishing_log_user_friend.user_friend_id')
            ->count('fishing_log_user_friend.user_friend_id');

        return $uniqueFriends >= $requiredCount;
    }

    /**
     * Check loyal location requirement (same spot X days in a year)
     */
    protected function checkLoyalLocationRequirement(int $requiredDays, User $user): bool
    {
        // Find the location with the most unique fishing days in any single year
        $maxDays = \DB::table('fishing_logs')
            ->where('user_id', $user->id)
            ->whereNotNull('user_location_id')
            ->selectRaw('user_location_id, YEAR(date) as year, COUNT(DISTINCT date) as days')
            ->groupBy('user_location_id', \DB::raw('YEAR(date)'))
            ->orderByDesc('days')
            ->first();

        return $maxDays && $maxDays->days >= $requiredDays;
    }

    /**
     * Check monthly consistency requirement (fished every month for X consecutive months)
     */
    protected function checkMonthlyConsistencyRequirement(int $requiredMonths, User $user): bool
    {
        // Get all months where user fished
        $months = FishingLog::where('user_id', $user->id)
            ->selectRaw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month')
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        if (count($months) < $requiredMonths) {
            return false;
        }

        // Check for consecutive months
        $maxConsecutive = 1;
        $currentConsecutive = 1;

        for ($i = 1; $i < count($months); $i++) {
            $prevDate = \Carbon\Carbon::createFromFormat('Y-m', $months[$i - 1]);
            $currDate = \Carbon\Carbon::createFromFormat('Y-m', $months[$i]);

            if ($prevDate->addMonth()->format('Y-m') === $currDate->format('Y-m')) {
                $currentConsecutive++;
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
            } else {
                $currentConsecutive = 1;
            }
        }

        return $maxConsecutive >= $requiredMonths;
    }

    /**
     * Check weekly consistency requirement (fished every week for X consecutive weeks)
     */
    protected function checkWeeklyConsistencyRequirement(int $requiredWeeks, User $user): bool
    {
        // Get all weeks where user fished
        $weeks = FishingLog::where('user_id', $user->id)
            ->selectRaw('DISTINCT YEARWEEK(date, 1) as week')
            ->orderBy('week')
            ->pluck('week')
            ->toArray();

        if (count($weeks) < $requiredWeeks) {
            return false;
        }

        // Check for consecutive weeks
        $maxConsecutive = 1;
        $currentConsecutive = 1;

        for ($i = 1; $i < count($weeks); $i++) {
            // YEARWEEK returns YYYYWW format
            $prevWeek = (int) $weeks[$i - 1];
            $currWeek = (int) $weeks[$i];

            // Check if weeks are consecutive (handling year boundaries)
            $prevYear = (int) floor($prevWeek / 100);
            $prevWeekNum = $prevWeek % 100;
            $currYear = (int) floor($currWeek / 100);
            $currWeekNum = $currWeek % 100;

            $isConsecutive = false;
            if ($currYear === $prevYear && $currWeekNum === $prevWeekNum + 1) {
                $isConsecutive = true;
            } elseif ($currYear === $prevYear + 1 && $currWeekNum === 1 && $prevWeekNum >= 52) {
                $isConsecutive = true;
            }

            if ($isConsecutive) {
                $currentConsecutive++;
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
            } else {
                $currentConsecutive = 1;
            }
        }

        return $maxConsecutive >= $requiredWeeks;
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

