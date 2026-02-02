<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardPreference extends Model
{
    use HasFactory;

    protected $table = 'user_dashboard_preferences';

    protected $fillable = [
        'user_id',
        'card_id',
        'order',
        'is_visible',
        'size',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order' => 'integer',
        'size' => 'integer',
    ];

    /**
     * Get the user that owns this dashboard preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all dashboard card identifiers with their default order.
     * This defines the available cards and their default positions.
     * Size values: 3 = 1/4, 4 = 1/3, 6 = 1/2, 8 = 2/3, 9 = 3/4, 12 = Full (based on 12-column grid)
     */
    public static function getDefaultCards(): array
    {
        return [
            ['card_id' => 'stats_total_catches', 'order' => 1, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'stats_favorite_location', 'order' => 2, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'stats_top_species', 'order' => 3, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'stats_fishing_buddies', 'order' => 4, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'biggest_catch', 'order' => 5, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'runner_up', 'order' => 6, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'species_pie_chart', 'order' => 7, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'favorite_weekday', 'order' => 8, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'longest_streak', 'order' => 9, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'average_per_trip', 'order' => 10, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'days_fished', 'order' => 11, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'successful_days', 'order' => 12, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'days_skunked', 'order' => 13, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_in_day', 'order' => 14, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_month', 'order' => 15, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_by_moon_phase', 'order' => 16, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_by_sun_phase', 'order' => 17, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'most_successful_fly', 'order' => 18, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'biggest_fish_fly', 'order' => 19, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_successful_fly_type', 'order' => 20, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_successful_fly_color', 'order' => 21, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'top_species_by_count', 'order' => 22, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'top_species_by_size', 'order' => 23, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'top_locations_by_count', 'order' => 24, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'top_locations_by_size', 'order' => 25, 'is_visible' => true, 'size' => 6],
        ];
    }

    /**
     * Get card size configuration for grid layout.
     * Size values: 3 = 1/4, 4 = 1/3, 6 = 1/2, 8 = 2/3, 9 = 3/4, 12 = Full (based on 12-column grid)
     */
    public static function getCardSizes(): array
    {
        return [
            'stats_total_catches' => 3,
            'stats_favorite_location' => 3,
            'stats_top_species' => 3,
            'stats_fishing_buddies' => 3,
            'biggest_catch' => 3,
            'runner_up' => 3,
            'species_pie_chart' => 6,
            'favorite_weekday' => 4,
            'longest_streak' => 4,
            'average_per_trip' => 4,
            'days_fished' => 3,
            'successful_days' => 3,
            'days_skunked' => 3,
            'most_in_day' => 3,
            'catches_by_month' => 4,
            'catches_by_moon_phase' => 4,
            'catches_by_sun_phase' => 4,
            'most_successful_fly' => 3,
            'biggest_fish_fly' => 3,
            'most_successful_fly_type' => 3,
            'most_successful_fly_color' => 3,
            'top_species_by_count' => 6,
            'top_species_by_size' => 6,
            'top_locations_by_count' => 6,
            'top_locations_by_size' => 6,
        ];
    }

    /**
     * Get card display names for the UI.
     */
    public static function getCardDisplayNames(): array
    {
        return [
            'stats_total_catches' => 'Total Catches',
            'stats_favorite_location' => 'Favorite Location',
            'stats_top_species' => 'Top Species',
            'stats_fishing_buddies' => 'Fishing Buddies',
            'biggest_catch' => 'Biggest Catch',
            'runner_up' => 'Runner Up',
            'species_pie_chart' => 'Species Distribution',
            'favorite_weekday' => 'Favorite Weekday',
            'longest_streak' => 'Longest Streak',
            'average_per_trip' => 'Average per Trip',
            'days_fished' => 'Days Fished',
            'successful_days' => 'Successful Days',
            'days_skunked' => 'Days Skunked',
            'most_in_day' => 'Most in a Day',
            'catches_by_month' => 'Catches by Month',
            'catches_by_moon_phase' => 'Catches by Moon Phase',
            'catches_by_sun_phase' => 'Catches by Sun Phase',
            'most_successful_fly' => 'Most Successful Fly',
            'biggest_fish_fly' => 'Biggest Fish Fly',
            'most_successful_fly_type' => 'Most Successful Fly Type',
            'most_successful_fly_color' => 'Most Successful Fly Color',
            'top_species_by_count' => 'Top Species by Count',
            'top_species_by_size' => 'Top Species by Size',
            'top_locations_by_count' => 'Top Locations by Count',
            'top_locations_by_size' => 'Top Locations by Size',
        ];
    }
}

