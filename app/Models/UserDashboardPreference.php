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
        // Default layout based on user 1's optimized configuration (95 cards)
        return [
            ['card_id' => 'stats_total_catches', 'order' => 1, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'average_per_trip', 'order' => 2, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'quantity_vs_quality', 'order' => 3, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'species_by_water_type', 'order' => 4, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'biggest_catch', 'order' => 5, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'runner_up', 'order' => 6, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'species_pie_chart', 'order' => 7, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'days_fished', 'order' => 8, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'successful_days', 'order' => 9, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'days_skunked', 'order' => 10, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_in_day', 'order' => 11, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_successful_fly', 'order' => 12, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'biggest_fish_fly', 'order' => 13, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_successful_fly_type', 'order' => 14, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_successful_fly_color', 'order' => 15, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_month', 'order' => 16, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_by_moon_phase', 'order' => 17, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_by_sun_phase', 'order' => 18, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'top_species_by_count', 'order' => 19, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'top_species_by_size', 'order' => 20, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'heaviest_catch', 'order' => 21, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'avg_weight_trend', 'order' => 22, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'total_weight', 'order' => 23, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'avg_weight_by_species', 'order' => 24, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'seasonal_trends', 'order' => 25, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_location_by_season', 'order' => 26, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'yoy_comparison', 'order' => 27, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'fishing_frequency', 'order' => 28, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_moon_position', 'order' => 29, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'major_vs_minor_feeding', 'order' => 30, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_moon_for_big_fish', 'order' => 31, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'location_variety', 'order' => 32, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'new_spot_success_rate', 'order' => 33, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'stats_favorite_location', 'order' => 34, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'underexplored_spots', 'order' => 35, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'top_locations_by_count', 'order' => 36, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'top_locations_by_size', 'order' => 37, 'is_visible' => true, 'size' => 6],
            ['card_id' => 'most_consistent_spot', 'order' => 38, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'multi_species_days', 'order' => 39, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'weekend_warrior', 'order' => 40, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'hot_streak', 'order' => 41, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_conditions_summary', 'order' => 42, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'golden_conditions', 'order' => 43, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_by_style', 'order' => 44, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catch_rate_trend', 'order' => 45, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'monthly_personal_bests', 'order' => 46, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'species_by_location', 'order' => 47, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'fly_color_by_conditions', 'order' => 48, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'rarest_catches', 'order' => 49, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'size_improvement', 'order' => 50, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'fishing_radius', 'order' => 51, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'freshwater_vs_saltwater', 'order' => 52, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'longest_streak', 'order' => 53, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'favorite_weekday', 'order' => 54, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_hour', 'order' => 55, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'fly_rotation', 'order' => 56, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_productive_buddy', 'order' => 57, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'lucky_charm_friend', 'order' => 58, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'stats_fishing_buddies', 'order' => 59, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'solo_vs_group', 'order' => 60, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'improvement_rate', 'order' => 61, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'avg_size_trend', 'order' => 62, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'states_pie_chart', 'order' => 63, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'countries_pie_chart', 'order' => 64, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_state_pie_chart', 'order' => 65, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_country_pie_chart', 'order' => 66, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_cloud_cover', 'order' => 67, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_wind_condition', 'order' => 68, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_precipitation', 'order' => 69, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_barometric_pressure', 'order' => 70, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_water_clarity', 'order' => 71, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_water_level', 'order' => 72, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_water_speed', 'order' => 73, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_surface_condition', 'order' => 74, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_tide', 'order' => 75, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'most_successful_rod', 'order' => 76, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'best_rod_for_trophies', 'order' => 77, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'consecutive_days_streak', 'order' => 78, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'one_hit_wonders', 'order' => 79, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'lucky_number', 'order' => 80, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'wind_cloud_combo', 'order' => 81, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'moon_time_combo', 'order' => 82, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'water_weather_combo', 'order' => 83, 'is_visible' => true, 'size' => 3],
            ['card_id' => 'catches_by_water_temp', 'order' => 84, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'catches_by_air_temp', 'order' => 85, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_fly_by_species', 'order' => 86, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_fly_by_location', 'order' => 87, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'best_fly_size', 'order' => 88, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'fly_size_by_species', 'order' => 89, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'fly_size_by_season', 'order' => 90, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'big_fish_air_temp', 'order' => 91, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'temp_sweet_spot', 'order' => 92, 'is_visible' => true, 'size' => 4],
            ['card_id' => 'badges', 'order' => 93, 'is_visible' => true, 'size' => 12],
        ];
    }

    /**
     * Get card size configuration for grid layout.
     * Size values: 3 = 1/4, 4 = 1/3, 6 = 1/2, 8 = 2/3, 9 = 3/4, 12 = Full (based on 12-column grid)
     * Default sizes based on user 1's optimized configuration
     */
    public static function getCardSizes(): array
    {
        return [
            // Key stats row
            'stats_total_catches' => 3,
            'stats_favorite_location' => 3,
            'stats_fishing_buddies' => 3,
            // Biggest catches + chart
            'biggest_catch' => 3,
            'runner_up' => 3,
            'species_pie_chart' => 6,
            // Trip stats
            'favorite_weekday' => 3,
            'longest_streak' => 3,
            'average_per_trip' => 3,
            // Day stats
            'days_fished' => 3,
            'successful_days' => 3,
            'days_skunked' => 3,
            'most_in_day' => 3,
            // Time-based charts
            'catches_by_month' => 4,
            'catches_by_moon_phase' => 4,
            'catches_by_sun_phase' => 4,
            // Fly stats
            'most_successful_fly' => 3,
            'biggest_fish_fly' => 3,
            'most_successful_fly_type' => 3,
            'most_successful_fly_color' => 3,
            // Species and location tables
            'top_species_by_count' => 6,
            'top_species_by_size' => 6,
            'top_locations_by_count' => 6,
            'top_locations_by_size' => 6,
            // Weather cards (all size 3 for consistent 4-column layout)
            'best_cloud_cover' => 3,
            'best_wind_condition' => 3,
            'catches_by_precipitation' => 3,
            'best_barometric_pressure' => 3,
            // Water condition cards (all size 3)
            'best_water_clarity' => 3,
            'catches_by_water_level' => 3,
            'best_water_speed' => 3,
            'best_surface_condition' => 3,
            // Moon position cards
            'catches_by_moon_position' => 4,
            'major_vs_minor_feeding' => 4,
            'best_moon_for_big_fish' => 4,
            // Tide + Weight cards (all size 3)
            'catches_by_tide' => 3,
            'heaviest_catch' => 3,
            'total_weight' => 3,
            'avg_weight_by_species' => 3,
            // Friend cards (all size 3)
            'most_productive_buddy' => 3,
            'solo_vs_group' => 3,
            'lucky_charm_friend' => 3,
            // Rod and style cards
            'most_successful_rod' => 3,
            'best_rod_for_trophies' => 3,
            'catches_by_style' => 4,
            // Combined analysis cards
            'golden_conditions' => 4,
            'best_conditions_summary' => 4,
            // Time & Pattern Analysis cards (all size 3)
            'best_hour' => 3,
            'seasonal_trends' => 3,
            'consecutive_days_streak' => 3,
            // Location Intelligence cards (all size 3)
            'location_variety' => 3,
            'most_consistent_spot' => 3,
            'underexplored_spots' => 3,
            'best_location_by_season' => 3,
            'new_spot_success_rate' => 3,
            // Species Deep Dive cards (all size 3)
            'rarest_catches' => 3,
            'size_improvement' => 3,
            // Fly/Lure Pattern cards
            'fly_rotation' => 3,
            'one_hit_wonders' => 3,
            'best_fly_by_location' => 4,
            'best_fly_by_species' => 4,
            // Progress & Goals cards
            'yoy_comparison' => 3,
            'improvement_rate' => 4,
            'avg_size_trend' => 4,
            'avg_weight_trend' => 3,
            'fishing_frequency' => 3,
            // Environmental Combo cards (all size 3)
            'wind_cloud_combo' => 3,
            'moon_time_combo' => 3,
            'water_weather_combo' => 3,
            // Gamification cards
            'badges' => 12,
            'hot_streak' => 3,
            'lucky_number' => 3,
            // Temperature cards
            'temp_sweet_spot' => 4,
            'big_fish_air_temp' => 4,
            'catches_by_air_temp' => 4,
            'catches_by_water_temp' => 4,
            // Fly size cards
            'best_fly_size' => 4,
            'fly_size_by_species' => 4,
            'fly_size_by_season' => 4,
            // Geographic cards
            'fishing_radius' => 3,
            'freshwater_vs_saltwater' => 3,
            'species_by_water_type' => 3,
            'states_pie_chart' => 4,
            'countries_pie_chart' => 4,
            'catches_state_pie_chart' => 4,
            'catches_country_pie_chart' => 4,
            // Additional analysis cards
            'weekend_warrior' => 3,
            'monthly_personal_bests' => 3,
            'catch_rate_trend' => 3,
            'species_by_location' => 3,
            'fly_color_by_conditions' => 3,
            'multi_species_days' => 3,
            'quantity_vs_quality' => 3,
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
            'stats_fishing_buddies' => 'Fishing Buddies',
            'biggest_catch' => 'Biggest Catch',
            'runner_up' => 'Runner Up',
            'species_pie_chart' => 'Species Distribution',
            'favorite_weekday' => 'Favorite Weekday',
            'longest_streak' => 'Consecutive Days Streak',
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
            // Combined analysis cards
            'golden_conditions' => 'Golden Conditions',
            'best_conditions_summary' => 'Trophy Conditions',
            // Time & Pattern Analysis cards
            'best_hour' => 'Best Hour of Day',
            'seasonal_trends' => 'Seasonal Trends',
            // Location Intelligence cards
            'location_variety' => 'Location Variety',
            'most_consistent_spot' => 'Most Consistent Spot',
            'underexplored_spots' => 'Underexplored Spots',
            'best_location_by_season' => 'Best Location by Season',
            'new_spot_success_rate' => 'New Spot Success Rate',
            // Species Deep Dive cards
            'rarest_catches' => 'Rarest Catches',
            'size_improvement' => 'Size Improvement',
            // Fly/Lure Pattern cards
            'fly_rotation' => 'Fly Rotation',
            'one_hit_wonders' => 'One-Hit Wonders',
            'best_fly_by_location' => 'Best Fly by Location',
            'best_fly_by_species' => 'Best Fly by Species',
            // Progress & Goals cards
            'yoy_comparison' => 'Year-over-Year Comparison',
            'improvement_rate' => 'Improvement Rate',
            'avg_size_trend' => 'Avg Size Trend',
            'avg_weight_trend' => 'Avg Weight Trend',
            'fishing_frequency' => 'Fishing Frequency',
            // Environmental Combo cards
            'wind_cloud_combo' => 'Wind + Cloud Combo',
            'moon_time_combo' => 'Moon + Time Combo',
            'water_weather_combo' => 'Water + Weather Combo',
            // Gamification cards
            'badges' => 'Achievement Badges',
            'hot_streak' => 'Hot Streak',
            'lucky_number' => 'Lucky Number',
            // Temperature cards
            'temp_sweet_spot' => 'Temperature Sweet Spot',
            'big_fish_air_temp' => 'Big Fish Temperature',
            'catches_by_air_temp' => 'Catches by Air Temp',
            'catches_by_water_temp' => 'Catches by Water Temp',
            // Fly size cards
            'best_fly_size' => 'Best Fly Size',
            'fly_size_by_species' => 'Fly Size by Species',
            'fly_size_by_season' => 'Fly Size by Season',
            // Geographic cards
            'fishing_radius' => 'Fishing Radius',
            'freshwater_vs_saltwater' => 'Freshwater vs Saltwater',
            'species_by_water_type' => 'Species by Water Type',
            'states_pie_chart' => 'Species by State',
            'countries_pie_chart' => 'Species by Country',
            'catches_state_pie_chart' => 'Catches by State',
            'catches_country_pie_chart' => 'Catches by Country',
            // Additional analysis cards
            'weekend_warrior' => 'Weekend Warrior',
            'monthly_personal_bests' => 'Monthly Personal Bests',
            'catch_rate_trend' => 'Catch Rate Trend',
            'species_by_location' => 'Species by Location',
            'fly_color_by_conditions' => 'Fly Color by Conditions',
            'multi_species_days' => 'Multi-Species Days',
            'quantity_vs_quality' => 'Quantity vs Quality',
        ];
    }
}

