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
            // Weather cards
            ['card_id' => 'best_cloud_cover', 'order' => 26, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_wind_condition', 'order' => 27, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_precipitation', 'order' => 28, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_barometric_pressure', 'order' => 29, 'is_visible' => true, 'size' => 3],
            // Water condition cards
            ['card_id' => 'best_water_clarity', 'order' => 30, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_water_level', 'order' => 31, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_water_speed', 'order' => 32, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_surface_condition', 'order' => 33, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_tide', 'order' => 34, 'is_visible' => true, 'size' => 4],
            // Moon position cards (Solunar)
            ['card_id' => 'catches_by_moon_position', 'order' => 35, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'major_vs_minor_feeding', 'order' => 36, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_moon_for_big_fish', 'order' => 37, 'is_visible' => true, 'size' => 4],
            // Weight cards
            ['card_id' => 'heaviest_catch', 'order' => 38, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'total_weight', 'order' => 39, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'avg_weight_by_species', 'order' => 40, 'is_visible' => true, 'size' => 3],
            // Friend cards
            ['card_id' => 'most_productive_buddy', 'order' => 41, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'solo_vs_group', 'order' => 42, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'lucky_charm_friend', 'order' => 43, 'is_visible' => true, 'size' => 3],
            // Rod and style cards
            ['card_id' => 'most_successful_rod', 'order' => 44, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_rod_for_trophies', 'order' => 45, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_style', 'order' => 46, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'most_successful_style', 'order' => 47, 'is_visible' => true, 'size' => 3],
            // Combined analysis cards
            ['card_id' => 'golden_conditions', 'order' => 48, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'best_conditions_summary', 'order' => 49, 'is_visible' => true, 'size' => 6],
            // Time & Pattern Analysis cards
            ['card_id' => 'best_hour', 'order' => 50, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'time_blocks', 'order' => 51, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_day_of_month', 'order' => 52, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'seasonal_trends', 'order' => 53, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'consecutive_days_streak', 'order' => 54, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'days_since_skunk', 'order' => 55, 'is_visible' => true, 'size' => 3],
            // Location Intelligence cards
            ['card_id' => 'location_variety', 'order' => 56, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_consistent_spot', 'order' => 57, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'underexplored_spots', 'order' => 58, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_location_by_season', 'order' => 59, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'new_spot_success_rate', 'order' => 60, 'is_visible' => true, 'size' => 3],
            // Species Deep Dive cards
            ['card_id' => 'species_diversity', 'order' => 61, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'rarest_catches', 'order' => 62, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'species_streak', 'order' => 63, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'new_species_this_year', 'order' => 64, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'size_improvement', 'order' => 65, 'is_visible' => true, 'size' => 4],
            // Fly/Lure Pattern cards
            ['card_id' => 'fly_rotation', 'order' => 66, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'one_hit_wonders', 'order' => 67, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'reliable_producers', 'order' => 68, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_fly_by_location', 'order' => 69, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'best_fly_by_species', 'order' => 70, 'is_visible' => true, 'size' => 6],
            // Progress & Goals cards
            ['card_id' => 'yoy_comparison', 'order' => 71, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'personal_bests', 'order' => 72, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'improvement_rate', 'order' => 73, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'fishing_frequency', 'order' => 74, 'is_visible' => true, 'size' => 6],
            // Environmental Combo cards
            ['card_id' => 'wind_cloud_combo', 'order' => 75, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'moon_time_combo', 'order' => 76, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'water_weather_combo', 'order' => 77, 'is_visible' => true, 'size' => 4],
            // Gamification cards
            ['card_id' => 'fishing_score', 'order' => 78, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'badges', 'order' => 79, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'hot_streak', 'order' => 80, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'lucky_number', 'order' => 81, 'is_visible' => true, 'size' => 3],
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
            // Weather cards
            'best_cloud_cover' => 3,
            'best_wind_condition' => 3,
            'catches_by_precipitation' => 4,
            'best_barometric_pressure' => 3,
            // Water condition cards
            'best_water_clarity' => 3,
            'catches_by_water_level' => 4,
            'best_water_speed' => 3,
            'best_surface_condition' => 3,
            'catches_by_tide' => 4,
            // Moon position cards (Solunar)
            'catches_by_moon_position' => 4,
            'major_vs_minor_feeding' => 4,
            'best_moon_for_big_fish' => 4,
            // Weight cards
            'heaviest_catch' => 3,
            'total_weight' => 3,
            'avg_weight_by_species' => 3,
            // Friend cards
            'most_productive_buddy' => 3,
            'solo_vs_group' => 4,
            'lucky_charm_friend' => 3,
            // Rod and style cards
            'most_successful_rod' => 3,
            'best_rod_for_trophies' => 3,
            'catches_by_style' => 4,
            'most_successful_style' => 3,
            // Combined analysis cards
            'golden_conditions' => 6,
            'best_conditions_summary' => 6,
            // Time & Pattern Analysis cards
            'best_hour' => 3,
            'time_blocks' => 4,
            'best_day_of_month' => 3,
            'seasonal_trends' => 6,
            'consecutive_days_streak' => 3,
            'days_since_skunk' => 3,
            // Location Intelligence cards
            'location_variety' => 3,
            'most_consistent_spot' => 4,
            'underexplored_spots' => 3,
            'best_location_by_season' => 6,
            'new_spot_success_rate' => 3,
            // Species Deep Dive cards
            'species_diversity' => 3,
            'rarest_catches' => 4,
            'species_streak' => 3,
            'new_species_this_year' => 3,
            'size_improvement' => 4,
            // Fly/Lure Pattern cards
            'fly_rotation' => 3,
            'one_hit_wonders' => 4,
            'reliable_producers' => 4,
            'best_fly_by_location' => 6,
            'best_fly_by_species' => 6,
            // Progress & Goals cards
            'yoy_comparison' => 6,
            'personal_bests' => 6,
            'improvement_rate' => 3,
            'fishing_frequency' => 6,
            // Environmental Combo cards
            'wind_cloud_combo' => 4,
            'moon_time_combo' => 4,
            'water_weather_combo' => 4,
            // Gamification cards
            'fishing_score' => 3,
            'badges' => 6,
            'hot_streak' => 3,
            'lucky_number' => 3,
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
            // Weather cards
            'best_cloud_cover' => 'Best Cloud Cover',
            'best_wind_condition' => 'Best Wind Condition',
            'catches_by_precipitation' => 'Catches by Precipitation',
            'best_barometric_pressure' => 'Best Barometric Pressure',
            // Water condition cards
            'best_water_clarity' => 'Best Water Clarity',
            'catches_by_water_level' => 'Catches by Water Level',
            'best_water_speed' => 'Best Water Speed',
            'best_surface_condition' => 'Best Surface Condition',
            'catches_by_tide' => 'Catches by Tide',
            // Moon position cards (Solunar)
            'catches_by_moon_position' => 'Catches by Moon Position',
            'major_vs_minor_feeding' => 'Major vs Minor Feeding',
            'best_moon_for_big_fish' => 'Best Moon for Big Fish',
            // Weight cards
            'heaviest_catch' => 'Heaviest Catch',
            'total_weight' => 'Total Weight Caught',
            'avg_weight_by_species' => 'Avg Weight by Species',
            // Friend cards
            'most_productive_buddy' => 'Most Productive Buddy',
            'solo_vs_group' => 'Solo vs Group Fishing',
            'lucky_charm_friend' => 'Lucky Charm Friend',
            // Rod and style cards
            'most_successful_rod' => 'Most Successful Rod',
            'best_rod_for_trophies' => 'Best Rod for Trophies',
            'catches_by_style' => 'Catches by Style',
            'most_successful_style' => 'Most Successful Style',
            // Combined analysis cards
            'golden_conditions' => 'Golden Conditions',
            'best_conditions_summary' => 'Best Conditions Summary',
            // Time & Pattern Analysis cards
            'best_hour' => 'Best Hour of Day',
            'time_blocks' => 'Morning vs Afternoon vs Evening',
            'best_day_of_month' => 'Best Day of Month',
            'seasonal_trends' => 'Seasonal Trends',
            'consecutive_days_streak' => 'Consecutive Days Streak',
            'days_since_skunk' => 'Days Since Last Skunk',
            // Location Intelligence cards
            'location_variety' => 'Location Variety',
            'most_consistent_spot' => 'Most Consistent Spot',
            'underexplored_spots' => 'Underexplored Spots',
            'best_location_by_season' => 'Best Location by Season',
            'new_spot_success_rate' => 'New Spot Success Rate',
            // Species Deep Dive cards
            'species_diversity' => 'Species Diversity',
            'rarest_catches' => 'Rarest Catches',
            'species_streak' => 'Most Caught Species',
            'new_species_this_year' => 'New Species This Year',
            'size_improvement' => 'Size Improvement',
            // Fly/Lure Pattern cards
            'fly_rotation' => 'Fly Rotation',
            'one_hit_wonders' => 'One-Hit Wonders',
            'reliable_producers' => 'Reliable Producers',
            'best_fly_by_location' => 'Best Fly by Location',
            'best_fly_by_species' => 'Best Fly by Species',
            // Progress & Goals cards
            'yoy_comparison' => 'Year-over-Year Comparison',
            'personal_bests' => 'Personal Bests',
            'improvement_rate' => 'Improvement Rate',
            'fishing_frequency' => 'Fishing Frequency',
            // Environmental Combo cards
            'wind_cloud_combo' => 'Wind + Cloud Combo',
            'moon_time_combo' => 'Moon + Time Combo',
            'water_weather_combo' => 'Water + Weather Combo',
            // Gamification cards
            'fishing_score' => 'Fishing Score',
            'badges' => 'Achievement Badges',
            'hot_streak' => 'Hot Streak',
            'lucky_number' => 'Lucky Number',
        ];
    }
}

