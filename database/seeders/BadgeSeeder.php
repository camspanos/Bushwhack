<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = array_merge(
            $this->getQuantityBadges(),
            $this->getSizeBadges(),
            $this->getTimeBadges(),
            $this->getWeatherBadges(),
            $this->getMoonBadges(),
            $this->getLocationBadges(),
            $this->getSpeciesBadges(),
            $this->getStreakBadges(),
            $this->getSeasonalBadges(),
            $this->getRodBadges(),
            $this->getFlyBadges(),
            $this->getWeightBadges(),
            $this->getComboBadges(),
            $this->getMilestoneBadges(),
            $this->getChallengeBadges(),
        );

        $sortOrder = 0;
        foreach ($badges as $badge) {
            $badge['slug'] = Str::slug($badge['name']);
            $badge['sort_order'] = $sortOrder++;
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }

    private function getQuantityBadges(): array
    {
        return [
            ['name' => 'First Catch', 'icon' => '🎣', 'description' => 'Catch your first fish', 'category' => 'quantity', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Getting Started', 'icon' => '🐟', 'description' => 'Catch 5 fish', 'category' => 'quantity', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Double Digits', 'icon' => '🔟', 'description' => 'Catch 10 fish', 'category' => 'quantity', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Quarter Century', 'icon' => '🎯', 'description' => 'Catch 25 fish', 'category' => 'quantity', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 25],
            ['name' => 'Half Century', 'icon' => '5️⃣0️⃣', 'description' => 'Catch 50 fish', 'category' => 'quantity', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Century Club', 'icon' => '💯', 'description' => 'Catch 100 fish', 'category' => 'quantity', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Two Hundred Club', 'icon' => '🏅', 'description' => 'Catch 200 fish', 'category' => 'quantity', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 200],
            ['name' => 'High Roller', 'icon' => '🎰', 'description' => 'Catch 500 fish', 'category' => 'quantity', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 500],
            ['name' => 'Grand Master', 'icon' => '👑', 'description' => 'Catch 1,000 fish', 'category' => 'quantity', 'rarity' => 'epic', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 1000],
            ['name' => 'Fish Whisperer', 'icon' => '🧙', 'description' => 'Catch 2,500 fish', 'category' => 'quantity', 'rarity' => 'epic', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 2500],
            ['name' => 'Legendary Angler', 'icon' => '⭐', 'description' => 'Catch 5,000 fish', 'category' => 'quantity', 'rarity' => 'legendary', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 5000],
            ['name' => 'Master of the Waters', 'icon' => '🌊', 'description' => 'Catch 10,000 fish', 'category' => 'quantity', 'rarity' => 'legendary', 'requirement_type' => 'count', 'requirement_field' => 'total_caught', 'requirement_operator' => '>=', 'requirement_value' => 10000],
            ['name' => 'Daily Limit', 'icon' => '📊', 'description' => 'Catch 10+ fish in a single day', 'category' => 'quantity', 'rarity' => 'uncommon', 'requirement_type' => 'daily_max', 'requirement_field' => 'quantity', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Hot Hand', 'icon' => '🔥', 'description' => 'Catch 20+ fish in a single day', 'category' => 'quantity', 'rarity' => 'rare', 'requirement_type' => 'daily_max', 'requirement_field' => 'quantity', 'requirement_operator' => '>=', 'requirement_value' => 20],
            ['name' => 'On Fire', 'icon' => '🌋', 'description' => 'Catch 50+ fish in a single day', 'category' => 'quantity', 'rarity' => 'epic', 'requirement_type' => 'daily_max', 'requirement_field' => 'quantity', 'requirement_operator' => '>=', 'requirement_value' => 50],
        ];
    }

    private function getSizeBadges(): array
    {
        return [
            ['name' => 'Size Matters', 'icon' => '📏', 'description' => 'Catch a fish 6" or longer', 'category' => 'size', 'rarity' => 'common', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 6],
            ['name' => 'Foot Long', 'icon' => '🦶', 'description' => 'Catch a fish 12" or longer', 'category' => 'size', 'rarity' => 'common', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 12],
            ['name' => 'Nice Catch', 'icon' => '👍', 'description' => 'Catch a fish 16" or longer', 'category' => 'size', 'rarity' => 'uncommon', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 16],
            ['name' => 'Trophy Hunter', 'icon' => '🏆', 'description' => 'Catch a fish 20" or longer', 'category' => 'size', 'rarity' => 'rare', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 20],
            ['name' => 'Monster Fish', 'icon' => '👹', 'description' => 'Catch a fish 24" or longer', 'category' => 'size', 'rarity' => 'rare', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 24],
            ['name' => 'Lunker', 'icon' => '🐋', 'description' => 'Catch a fish 30" or longer', 'category' => 'size', 'rarity' => 'epic', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 30],
            ['name' => 'Whale of a Fish', 'icon' => '🐳', 'description' => 'Catch a fish 36" or longer', 'category' => 'size', 'rarity' => 'epic', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 36],
            ['name' => 'Leviathan', 'icon' => '🦕', 'description' => 'Catch a fish 48" or longer', 'category' => 'size', 'rarity' => 'legendary', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 48],
            ['name' => 'Sea Monster', 'icon' => '🦑', 'description' => 'Catch a fish 60" or longer', 'category' => 'size', 'rarity' => 'legendary', 'requirement_type' => 'max', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 60],
            ['name' => 'Trophy Collector', 'icon' => '🏅', 'description' => 'Catch 5 fish over 20"', 'category' => 'size', 'rarity' => 'rare', 'requirement_type' => 'count_where', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 20, 'requirement_value2' => 5],
            ['name' => 'Big Game Hunter', 'icon' => '🎯', 'description' => 'Catch 10 fish over 20"', 'category' => 'size', 'rarity' => 'epic', 'requirement_type' => 'count_where', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 20, 'requirement_value2' => 10],
            ['name' => 'Trophy Room', 'icon' => '🏛️', 'description' => 'Catch 25 fish over 20"', 'category' => 'size', 'rarity' => 'legendary', 'requirement_type' => 'count_where', 'requirement_field' => 'max_size', 'requirement_operator' => '>=', 'requirement_value' => 20, 'requirement_value2' => 25],
        ];
    }

    private function getTimeBadges(): array
    {
        return [
            ['name' => 'Early Bird', 'icon' => '🌅', 'description' => 'Catch a fish before 6am', 'category' => 'time', 'rarity' => 'common', 'requirement_type' => 'time_range', 'requirement_field' => 'time', 'requirement_operator' => '<', 'requirement_value' => 6],
            ['name' => 'Dawn Patrol', 'icon' => '🌄', 'description' => 'Catch 10 fish before 6am', 'category' => 'time', 'rarity' => 'uncommon', 'requirement_type' => 'count_time', 'requirement_field' => 'time', 'requirement_operator' => '<', 'requirement_value' => 6, 'requirement_value2' => 10],
            ['name' => 'Sunrise Specialist', 'icon' => '☀️', 'description' => 'Catch 50 fish before 7am', 'category' => 'time', 'rarity' => 'rare', 'requirement_type' => 'count_time', 'requirement_field' => 'time', 'requirement_operator' => '<', 'requirement_value' => 7, 'requirement_value2' => 50],
            ['name' => 'Night Owl', 'icon' => '🦉', 'description' => 'Catch a fish after 9pm', 'category' => 'time', 'rarity' => 'common', 'requirement_type' => 'time_range', 'requirement_field' => 'time', 'requirement_operator' => '>=', 'requirement_value' => 21],
            ['name' => 'Midnight Angler', 'icon' => '🌙', 'description' => 'Catch 10 fish after 9pm', 'category' => 'time', 'rarity' => 'uncommon', 'requirement_type' => 'count_time', 'requirement_field' => 'time', 'requirement_operator' => '>=', 'requirement_value' => 21, 'requirement_value2' => 10],
            ['name' => 'Nocturnal Master', 'icon' => '🌑', 'description' => 'Catch 50 fish after 8pm', 'category' => 'time', 'rarity' => 'rare', 'requirement_type' => 'count_time', 'requirement_field' => 'time', 'requirement_operator' => '>=', 'requirement_value' => 20, 'requirement_value2' => 50],
            ['name' => 'Lunch Break', 'icon' => '🥪', 'description' => 'Catch a fish between 12pm-1pm', 'category' => 'time', 'rarity' => 'common', 'requirement_type' => 'time_between', 'requirement_field' => 'time', 'requirement_operator' => 'between', 'requirement_value' => 12, 'requirement_value2' => 13],
            ['name' => 'Golden Hour', 'icon' => '🌇', 'description' => 'Catch 25 fish during golden hour (6-8am or 6-8pm)', 'category' => 'time', 'rarity' => 'rare', 'requirement_type' => 'count_golden_hour', 'requirement_field' => 'time', 'requirement_operator' => 'golden', 'requirement_value' => 25],
            ['name' => 'All Day Angler', 'icon' => '⏰', 'description' => 'Catch fish in morning, afternoon, and evening in one day', 'category' => 'time', 'rarity' => 'rare', 'requirement_type' => 'time_variety', 'requirement_field' => 'time', 'requirement_operator' => 'all_periods', 'requirement_value' => 1],
        ];
    }

    private function getWeatherBadges(): array
    {
        return [
            ['name' => 'Rain Fisher', 'icon' => '🌧️', 'description' => 'Catch a fish in the rain', 'category' => 'weather', 'rarity' => 'common', 'requirement_type' => 'weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['weather' => 'rain']],
            ['name' => 'Storm Chaser', 'icon' => '⛈️', 'description' => 'Catch 10 fish in rainy conditions', 'category' => 'weather', 'rarity' => 'uncommon', 'requirement_type' => 'count_weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 10, 'requirement_extra' => ['weather' => 'rain']],
            ['name' => 'Monsoon Master', 'icon' => '🌊', 'description' => 'Catch 50 fish in rainy conditions', 'category' => 'weather', 'rarity' => 'rare', 'requirement_type' => 'count_weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['weather' => 'rain']],
            ['name' => 'Snow Angler', 'icon' => '❄️', 'description' => 'Catch a fish in snowy conditions', 'category' => 'weather', 'rarity' => 'uncommon', 'requirement_type' => 'weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['weather' => 'snow']],
            ['name' => 'Blizzard Brave', 'icon' => '🌨️', 'description' => 'Catch 10 fish in snowy conditions', 'category' => 'weather', 'rarity' => 'rare', 'requirement_type' => 'count_weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 10, 'requirement_extra' => ['weather' => 'snow']],
            ['name' => 'Sunny Side Up', 'icon' => '☀️', 'description' => 'Catch 25 fish on sunny days', 'category' => 'weather', 'rarity' => 'common', 'requirement_type' => 'count_weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 25, 'requirement_extra' => ['weather' => 'sunny']],
            ['name' => 'Cloud Cover', 'icon' => '☁️', 'description' => 'Catch 25 fish on cloudy days', 'category' => 'weather', 'rarity' => 'common', 'requirement_type' => 'count_weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 25, 'requirement_extra' => ['weather' => 'cloudy']],
            ['name' => 'Overcast Expert', 'icon' => '🌥️', 'description' => 'Catch 100 fish on overcast days', 'category' => 'weather', 'rarity' => 'rare', 'requirement_type' => 'count_weather', 'requirement_field' => 'weather', 'requirement_operator' => '=', 'requirement_value' => 100, 'requirement_extra' => ['weather' => 'overcast']],
            ['name' => 'Wind Warrior', 'icon' => '💨', 'description' => 'Catch 25 fish on windy days (10+ mph)', 'category' => 'weather', 'rarity' => 'uncommon', 'requirement_type' => 'count_wind', 'requirement_field' => 'wind_speed', 'requirement_operator' => '>=', 'requirement_value' => 10, 'requirement_value2' => 25],
            ['name' => 'Gale Force', 'icon' => '🌬️', 'description' => 'Catch a fish in 20+ mph winds', 'category' => 'weather', 'rarity' => 'rare', 'requirement_type' => 'wind', 'requirement_field' => 'wind_speed', 'requirement_operator' => '>=', 'requirement_value' => 20],
            ['name' => 'Calm Waters', 'icon' => '🪷', 'description' => 'Catch 50 fish on calm days (wind < 5 mph)', 'category' => 'weather', 'rarity' => 'uncommon', 'requirement_type' => 'count_wind', 'requirement_field' => 'wind_speed', 'requirement_operator' => '<', 'requirement_value' => 5, 'requirement_value2' => 50],
            ['name' => 'Pressure Pro', 'icon' => '📊', 'description' => 'Catch fish in high pressure (>30.2 inHg)', 'category' => 'weather', 'rarity' => 'uncommon', 'requirement_type' => 'pressure', 'requirement_field' => 'pressure', 'requirement_operator' => '>', 'requirement_value' => 30.2],
            ['name' => 'Low Pressure Legend', 'icon' => '📉', 'description' => 'Catch 25 fish in low pressure (<29.8 inHg)', 'category' => 'weather', 'rarity' => 'rare', 'requirement_type' => 'count_pressure', 'requirement_field' => 'pressure', 'requirement_operator' => '<', 'requirement_value' => 29.8, 'requirement_value2' => 25],
        ];
    }

    private function getMoonBadges(): array
    {
        return [
            ['name' => 'Full Moon Fisher', 'icon' => '🌕', 'description' => 'Catch a fish during a full moon', 'category' => 'moon', 'rarity' => 'common', 'requirement_type' => 'moon_phase', 'requirement_field' => 'moon_phase', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['phase' => 'full']],
            ['name' => 'Lunar Legend', 'icon' => '🌝', 'description' => 'Catch 25 fish during full moons', 'category' => 'moon', 'rarity' => 'rare', 'requirement_type' => 'count_moon', 'requirement_field' => 'moon_phase', 'requirement_operator' => '=', 'requirement_value' => 25, 'requirement_extra' => ['phase' => 'full']],
            ['name' => 'New Moon Ninja', 'icon' => '🌑', 'description' => 'Catch a fish during a new moon', 'category' => 'moon', 'rarity' => 'common', 'requirement_type' => 'moon_phase', 'requirement_field' => 'moon_phase', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['phase' => 'new']],
            ['name' => 'Dark Side Master', 'icon' => '🌚', 'description' => 'Catch 25 fish during new moons', 'category' => 'moon', 'rarity' => 'rare', 'requirement_type' => 'count_moon', 'requirement_field' => 'moon_phase', 'requirement_operator' => '=', 'requirement_value' => 25, 'requirement_extra' => ['phase' => 'new']],
            ['name' => 'Waxing Wonder', 'icon' => '🌒', 'description' => 'Catch 10 fish during waxing moon', 'category' => 'moon', 'rarity' => 'uncommon', 'requirement_type' => 'count_moon', 'requirement_field' => 'moon_phase', 'requirement_operator' => '=', 'requirement_value' => 10, 'requirement_extra' => ['phase' => 'waxing']],
            ['name' => 'Waning Warrior', 'icon' => '🌘', 'description' => 'Catch 10 fish during waning moon', 'category' => 'moon', 'rarity' => 'uncommon', 'requirement_type' => 'count_moon', 'requirement_field' => 'moon_phase', 'requirement_operator' => '=', 'requirement_value' => 10, 'requirement_extra' => ['phase' => 'waning']],
            ['name' => 'Quarter Pounder', 'icon' => '🌓', 'description' => 'Catch fish during all 4 moon quarters', 'category' => 'moon', 'rarity' => 'rare', 'requirement_type' => 'moon_variety', 'requirement_field' => 'moon_phase', 'requirement_operator' => 'all', 'requirement_value' => 4],
            ['name' => 'Solunar Scientist', 'icon' => '🔬', 'description' => 'Catch 50 fish during major solunar periods', 'category' => 'moon', 'rarity' => 'epic', 'requirement_type' => 'count_solunar', 'requirement_field' => 'solunar_position', 'requirement_operator' => 'major', 'requirement_value' => 50],
            ['name' => 'Moon Position Master', 'icon' => '🎯', 'description' => 'Catch fish in all 6 moon positions', 'category' => 'moon', 'rarity' => 'epic', 'requirement_type' => 'moon_position_variety', 'requirement_field' => 'solunar_position', 'requirement_operator' => 'all', 'requirement_value' => 6],
        ];
    }

    private function getLocationBadges(): array
    {
        return [
            ['name' => 'Home Waters', 'icon' => '🏠', 'description' => 'Log your first fishing location', 'category' => 'location', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'location_count', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Explorer', 'icon' => '🗺️', 'description' => 'Fish at 5 different locations', 'category' => 'location', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'location_count', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Wanderer', 'icon' => '🧭', 'description' => 'Fish at 10 different locations', 'category' => 'location', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'location_count', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Adventurer', 'icon' => '🎒', 'description' => 'Fish at 25 different locations', 'category' => 'location', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'location_count', 'requirement_operator' => '>=', 'requirement_value' => 25],
            ['name' => 'Globetrotter', 'icon' => '🌍', 'description' => 'Fish at 50 different locations', 'category' => 'location', 'rarity' => 'epic', 'requirement_type' => 'count', 'requirement_field' => 'location_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'World Traveler', 'icon' => '✈️', 'description' => 'Fish at 100 different locations', 'category' => 'location', 'rarity' => 'legendary', 'requirement_type' => 'count', 'requirement_field' => 'location_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Loyal Local', 'icon' => '💚', 'description' => 'Fish the same location 25 times', 'category' => 'location', 'rarity' => 'uncommon', 'requirement_type' => 'location_visits', 'requirement_field' => 'location_max_visits', 'requirement_operator' => '>=', 'requirement_value' => 25],
            ['name' => 'Regular', 'icon' => '🔄', 'description' => 'Fish the same location 50 times', 'category' => 'location', 'rarity' => 'rare', 'requirement_type' => 'location_visits', 'requirement_field' => 'location_max_visits', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Home Base Hero', 'icon' => '🏆', 'description' => 'Fish the same location 100 times', 'category' => 'location', 'rarity' => 'epic', 'requirement_type' => 'location_visits', 'requirement_field' => 'location_max_visits', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'New Spot Success', 'icon' => '🆕', 'description' => 'Catch fish at a new location on first visit', 'category' => 'location', 'rarity' => 'uncommon', 'requirement_type' => 'new_spot_success', 'requirement_field' => 'new_location_catch', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Pioneer', 'icon' => '🚀', 'description' => 'Catch fish at 10 new locations on first visit', 'category' => 'location', 'rarity' => 'rare', 'requirement_type' => 'new_spot_success', 'requirement_field' => 'new_location_catch', 'requirement_operator' => '>=', 'requirement_value' => 10],
        ];
    }

    private function getSpeciesBadges(): array
    {
        return [
            ['name' => 'First Species', 'icon' => '🐟', 'description' => 'Log your first fish species', 'category' => 'species', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Species Sampler', 'icon' => '🎣', 'description' => 'Catch 3 different species', 'category' => 'species', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Variety Pack', 'icon' => '📦', 'description' => 'Catch 5 different species', 'category' => 'species', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Species Hunter', 'icon' => '🎯', 'description' => 'Catch 10 different species', 'category' => 'species', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Biodiversity Boss', 'icon' => '🌿', 'description' => 'Catch 15 different species', 'category' => 'species', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 15],
            ['name' => 'Species Collector', 'icon' => '📚', 'description' => 'Catch 20 different species', 'category' => 'species', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 20],
            ['name' => 'Ichthyologist', 'icon' => '🔬', 'description' => 'Catch 30 different species', 'category' => 'species', 'rarity' => 'epic', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 30],
            ['name' => 'Master Naturalist', 'icon' => '🏛️', 'description' => 'Catch 50 different species', 'category' => 'species', 'rarity' => 'legendary', 'requirement_type' => 'count', 'requirement_field' => 'species_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Species Specialist', 'icon' => '⭐', 'description' => 'Catch 50 of the same species', 'category' => 'species', 'rarity' => 'rare', 'requirement_type' => 'species_max', 'requirement_field' => 'species_max_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Species Master', 'icon' => '👑', 'description' => 'Catch 100 of the same species', 'category' => 'species', 'rarity' => 'epic', 'requirement_type' => 'species_max', 'requirement_field' => 'species_max_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Grand Slam', 'icon' => '🏆', 'description' => 'Catch 3 different species in one day', 'category' => 'species', 'rarity' => 'uncommon', 'requirement_type' => 'daily_species', 'requirement_field' => 'daily_species_count', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Super Slam', 'icon' => '💫', 'description' => 'Catch 5 different species in one day', 'category' => 'species', 'rarity' => 'rare', 'requirement_type' => 'daily_species', 'requirement_field' => 'daily_species_count', 'requirement_operator' => '>=', 'requirement_value' => 5],
        ];
    }

    private function getStreakBadges(): array
    {
        return [
            ['name' => 'Two-Timer', 'icon' => '2️⃣', 'description' => 'Fish 2 days in a row', 'category' => 'streak', 'rarity' => 'common', 'requirement_type' => 'streak', 'requirement_field' => 'fishing_streak', 'requirement_operator' => '>=', 'requirement_value' => 2],
            ['name' => 'Three-Peat', 'icon' => '3️⃣', 'description' => 'Fish 3 days in a row', 'category' => 'streak', 'rarity' => 'common', 'requirement_type' => 'streak', 'requirement_field' => 'fishing_streak', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Week Warrior', 'icon' => '📅', 'description' => 'Fish 7 days in a row', 'category' => 'streak', 'rarity' => 'uncommon', 'requirement_type' => 'streak', 'requirement_field' => 'fishing_streak', 'requirement_operator' => '>=', 'requirement_value' => 7],
            ['name' => 'Fortnight Fisher', 'icon' => '🗓️', 'description' => 'Fish 14 days in a row', 'category' => 'streak', 'rarity' => 'rare', 'requirement_type' => 'streak', 'requirement_field' => 'fishing_streak', 'requirement_operator' => '>=', 'requirement_value' => 14],
            ['name' => 'Monthly Madness', 'icon' => '📆', 'description' => 'Fish 30 days in a row', 'category' => 'streak', 'rarity' => 'epic', 'requirement_type' => 'streak', 'requirement_field' => 'fishing_streak', 'requirement_operator' => '>=', 'requirement_value' => 30],
            ['name' => 'Dedicated Angler', 'icon' => '💪', 'description' => 'Fish 60 days in a row', 'category' => 'streak', 'rarity' => 'epic', 'requirement_type' => 'streak', 'requirement_field' => 'fishing_streak', 'requirement_operator' => '>=', 'requirement_value' => 60],
            ['name' => 'Obsessed', 'icon' => '🤯', 'description' => 'Fish 100 days in a row', 'category' => 'streak', 'rarity' => 'legendary', 'requirement_type' => 'streak', 'requirement_field' => 'fishing_streak', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Catch Streak 3', 'icon' => '🔥', 'description' => 'Catch fish 3 days in a row', 'category' => 'streak', 'rarity' => 'common', 'requirement_type' => 'catch_streak', 'requirement_field' => 'catch_streak', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Catch Streak 7', 'icon' => '🔥', 'description' => 'Catch fish 7 days in a row', 'category' => 'streak', 'rarity' => 'uncommon', 'requirement_type' => 'catch_streak', 'requirement_field' => 'catch_streak', 'requirement_operator' => '>=', 'requirement_value' => 7],
            ['name' => 'Catch Streak 14', 'icon' => '🔥', 'description' => 'Catch fish 14 days in a row', 'category' => 'streak', 'rarity' => 'rare', 'requirement_type' => 'catch_streak', 'requirement_field' => 'catch_streak', 'requirement_operator' => '>=', 'requirement_value' => 14],
            ['name' => 'Catch Streak 30', 'icon' => '🔥', 'description' => 'Catch fish 30 days in a row', 'category' => 'streak', 'rarity' => 'epic', 'requirement_type' => 'catch_streak', 'requirement_field' => 'catch_streak', 'requirement_operator' => '>=', 'requirement_value' => 30],
            ['name' => 'No Skunk Week', 'icon' => '🦨', 'description' => 'Avoid getting skunked for 7 days', 'category' => 'streak', 'rarity' => 'uncommon', 'requirement_type' => 'no_skunk', 'requirement_field' => 'no_skunk_streak', 'requirement_operator' => '>=', 'requirement_value' => 7],
            ['name' => 'No Skunk Month', 'icon' => '🏅', 'description' => 'Avoid getting skunked for 30 days', 'category' => 'streak', 'rarity' => 'epic', 'requirement_type' => 'no_skunk', 'requirement_field' => 'no_skunk_streak', 'requirement_operator' => '>=', 'requirement_value' => 30],
        ];
    }

    private function getSeasonalBadges(): array
    {
        return [
            ['name' => 'Spring Fever', 'icon' => '🌸', 'description' => 'Catch a fish in spring', 'category' => 'seasonal', 'rarity' => 'common', 'requirement_type' => 'season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['season' => 'spring']],
            ['name' => 'Spring Specialist', 'icon' => '🌷', 'description' => 'Catch 50 fish in spring', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'count_season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['season' => 'spring']],
            ['name' => 'Summer Slammer', 'icon' => '☀️', 'description' => 'Catch a fish in summer', 'category' => 'seasonal', 'rarity' => 'common', 'requirement_type' => 'season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['season' => 'summer']],
            ['name' => 'Summer Specialist', 'icon' => '🏖️', 'description' => 'Catch 50 fish in summer', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'count_season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['season' => 'summer']],
            ['name' => 'Fall Fanatic', 'icon' => '🍂', 'description' => 'Catch a fish in fall', 'category' => 'seasonal', 'rarity' => 'common', 'requirement_type' => 'season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['season' => 'fall']],
            ['name' => 'Fall Specialist', 'icon' => '🍁', 'description' => 'Catch 50 fish in fall', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'count_season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['season' => 'fall']],
            ['name' => 'Winter Warrior', 'icon' => '❄️', 'description' => 'Catch a fish in winter', 'category' => 'seasonal', 'rarity' => 'common', 'requirement_type' => 'season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['season' => 'winter']],
            ['name' => 'Winter Specialist', 'icon' => '⛄', 'description' => 'Catch 50 fish in winter', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'count_season', 'requirement_field' => 'season', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['season' => 'winter']],
            ['name' => 'Four Seasons', 'icon' => '🌍', 'description' => 'Catch fish in all four seasons', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'season_variety', 'requirement_field' => 'seasons_fished', 'requirement_operator' => '=', 'requirement_value' => 4],
            ['name' => 'Year Round Angler', 'icon' => '📅', 'description' => 'Catch fish in every month of the year', 'category' => 'seasonal', 'rarity' => 'epic', 'requirement_type' => 'month_variety', 'requirement_field' => 'months_fished', 'requirement_operator' => '=', 'requirement_value' => 12],
            ['name' => 'Holiday Fisher', 'icon' => '🎄', 'description' => 'Catch a fish on a major holiday', 'category' => 'seasonal', 'rarity' => 'uncommon', 'requirement_type' => 'holiday', 'requirement_field' => 'holiday_catch', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'New Year Catch', 'icon' => '🎆', 'description' => 'Catch a fish on New Year\'s Day', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'specific_date', 'requirement_field' => 'date', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['month' => 1, 'day' => 1]],
            ['name' => 'Independence Angler', 'icon' => '🎇', 'description' => 'Catch a fish on July 4th', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'specific_date', 'requirement_field' => 'date', 'requirement_operator' => '=', 'requirement_value' => 1, 'requirement_extra' => ['month' => 7, 'day' => 4]],
            ['name' => 'Birthday Catch', 'icon' => '🎂', 'description' => 'Catch a fish on your birthday', 'category' => 'seasonal', 'rarity' => 'rare', 'requirement_type' => 'birthday', 'requirement_field' => 'birthday_catch', 'requirement_operator' => '>=', 'requirement_value' => 1],
        ];
    }

    private function getRodBadges(): array
    {
        return [
            ['name' => 'First Rod', 'icon' => '🎣', 'description' => 'Log your first rod', 'category' => 'rod', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'rod_count', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Rod Collector', 'icon' => '🗄️', 'description' => 'Own 5 different rods', 'category' => 'rod', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'rod_count', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Arsenal', 'icon' => '🏪', 'description' => 'Own 10 different rods', 'category' => 'rod', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'rod_count', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Rod Hoarder', 'icon' => '🏛️', 'description' => 'Own 25 different rods', 'category' => 'rod', 'rarity' => 'epic', 'requirement_type' => 'count', 'requirement_field' => 'rod_count', 'requirement_operator' => '>=', 'requirement_value' => 25],
            ['name' => 'Fly Rod Master', 'icon' => '🪶', 'description' => 'Catch 100 fish with fly rods', 'category' => 'rod', 'rarity' => 'rare', 'requirement_type' => 'count_rod_type', 'requirement_field' => 'rod_type', 'requirement_operator' => '=', 'requirement_value' => 100, 'requirement_extra' => ['type' => 'fly']],
            ['name' => 'Spin Master', 'icon' => '🔄', 'description' => 'Catch 100 fish with spinning rods', 'category' => 'rod', 'rarity' => 'rare', 'requirement_type' => 'count_rod_type', 'requirement_field' => 'rod_type', 'requirement_operator' => '=', 'requirement_value' => 100, 'requirement_extra' => ['type' => 'spinning']],
            ['name' => 'Baitcaster Pro', 'icon' => '🎯', 'description' => 'Catch 100 fish with baitcasting rods', 'category' => 'rod', 'rarity' => 'rare', 'requirement_type' => 'count_rod_type', 'requirement_field' => 'rod_type', 'requirement_operator' => '=', 'requirement_value' => 100, 'requirement_extra' => ['type' => 'baitcast']],
            ['name' => 'Versatile Angler', 'icon' => '🔀', 'description' => 'Catch fish with 5 different rods', 'category' => 'rod', 'rarity' => 'uncommon', 'requirement_type' => 'rod_variety', 'requirement_field' => 'rods_used', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Favorite Rod', 'icon' => '❤️', 'description' => 'Catch 50 fish with the same rod', 'category' => 'rod', 'rarity' => 'uncommon', 'requirement_type' => 'rod_max', 'requirement_field' => 'rod_max_catches', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Lucky Rod', 'icon' => '🍀', 'description' => 'Catch 200 fish with the same rod', 'category' => 'rod', 'rarity' => 'epic', 'requirement_type' => 'rod_max', 'requirement_field' => 'rod_max_catches', 'requirement_operator' => '>=', 'requirement_value' => 200],
        ];
    }

    private function getFlyBadges(): array
    {
        return [
            ['name' => 'First Fly', 'icon' => '🪰', 'description' => 'Log your first fly/lure', 'category' => 'fly', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'fly_count', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Fly Box', 'icon' => '📦', 'description' => 'Own 10 different flies/lures', 'category' => 'fly', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'fly_count', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Tackle Box', 'icon' => '🧰', 'description' => 'Own 25 different flies/lures', 'category' => 'fly', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'fly_count', 'requirement_operator' => '>=', 'requirement_value' => 25],
            ['name' => 'Fly Hoarder', 'icon' => '🗃️', 'description' => 'Own 50 different flies/lures', 'category' => 'fly', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'fly_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Fly Collector', 'icon' => '🏆', 'description' => 'Own 100 different flies/lures', 'category' => 'fly', 'rarity' => 'epic', 'requirement_type' => 'count', 'requirement_field' => 'fly_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Dry Fly Devotee', 'icon' => '🦋', 'description' => 'Catch 50 fish on dry flies', 'category' => 'fly', 'rarity' => 'rare', 'requirement_type' => 'count_fly_type', 'requirement_field' => 'fly_type', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['type' => 'dry']],
            ['name' => 'Nymph Ninja', 'icon' => '🐛', 'description' => 'Catch 50 fish on nymphs', 'category' => 'fly', 'rarity' => 'rare', 'requirement_type' => 'count_fly_type', 'requirement_field' => 'fly_type', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['type' => 'nymph']],
            ['name' => 'Streamer Slayer', 'icon' => '🦈', 'description' => 'Catch 50 fish on streamers', 'category' => 'fly', 'rarity' => 'rare', 'requirement_type' => 'count_fly_type', 'requirement_field' => 'fly_type', 'requirement_operator' => '=', 'requirement_value' => 50, 'requirement_extra' => ['type' => 'streamer']],
            ['name' => 'Go-To Fly', 'icon' => '⭐', 'description' => 'Catch 25 fish with the same fly', 'category' => 'fly', 'rarity' => 'uncommon', 'requirement_type' => 'fly_max', 'requirement_field' => 'fly_max_catches', 'requirement_operator' => '>=', 'requirement_value' => 25],
            ['name' => 'Magic Fly', 'icon' => '✨', 'description' => 'Catch 100 fish with the same fly', 'category' => 'fly', 'rarity' => 'epic', 'requirement_type' => 'fly_max', 'requirement_field' => 'fly_max_catches', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Pattern Master', 'icon' => '🎨', 'description' => 'Catch fish with 25 different flies', 'category' => 'fly', 'rarity' => 'rare', 'requirement_type' => 'fly_variety', 'requirement_field' => 'flies_used', 'requirement_operator' => '>=', 'requirement_value' => 25],
        ];
    }

    private function getWeightBadges(): array
    {
        return [
            ['name' => 'First Weigh-In', 'icon' => '⚖️', 'description' => 'Log your first fish weight', 'category' => 'weight', 'rarity' => 'common', 'requirement_type' => 'exists', 'requirement_field' => 'weight_logged', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Pounder', 'icon' => '1️⃣', 'description' => 'Catch a fish weighing 1+ lbs', 'category' => 'weight', 'rarity' => 'common', 'requirement_type' => 'max', 'requirement_field' => 'max_weight', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Two Pounder', 'icon' => '2️⃣', 'description' => 'Catch a fish weighing 2+ lbs', 'category' => 'weight', 'rarity' => 'uncommon', 'requirement_type' => 'max', 'requirement_field' => 'max_weight', 'requirement_operator' => '>=', 'requirement_value' => 2],
            ['name' => 'Five Pounder', 'icon' => '5️⃣', 'description' => 'Catch a fish weighing 5+ lbs', 'category' => 'weight', 'rarity' => 'rare', 'requirement_type' => 'max', 'requirement_field' => 'max_weight', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Ten Pounder', 'icon' => '🔟', 'description' => 'Catch a fish weighing 10+ lbs', 'category' => 'weight', 'rarity' => 'rare', 'requirement_type' => 'max', 'requirement_field' => 'max_weight', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Twenty Pounder', 'icon' => '💪', 'description' => 'Catch a fish weighing 20+ lbs', 'category' => 'weight', 'rarity' => 'epic', 'requirement_type' => 'max', 'requirement_field' => 'max_weight', 'requirement_operator' => '>=', 'requirement_value' => 20],
            ['name' => 'Fifty Pounder', 'icon' => '🏋️', 'description' => 'Catch a fish weighing 50+ lbs', 'category' => 'weight', 'rarity' => 'legendary', 'requirement_type' => 'max', 'requirement_field' => 'max_weight', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Hundred Pounder', 'icon' => '🐋', 'description' => 'Catch a fish weighing 100+ lbs', 'category' => 'weight', 'rarity' => 'legendary', 'requirement_type' => 'max', 'requirement_field' => 'max_weight', 'requirement_operator' => '>=', 'requirement_value' => 100],
        ];
    }

    private function getComboBadges(): array
    {
        return [
            ['name' => 'Perfect Morning', 'icon' => '🌅', 'description' => 'Catch a 20"+ fish before 7am', 'category' => 'combo', 'rarity' => 'rare', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['size' => 20, 'time_before' => 7]],
            ['name' => 'Moonlit Monster', 'icon' => '🌙', 'description' => 'Catch a 20"+ fish during full moon', 'category' => 'combo', 'rarity' => 'epic', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['size' => 20, 'moon_phase' => 'full']],
            ['name' => 'Storm Trophy', 'icon' => '⛈️', 'description' => 'Catch a 20"+ fish in the rain', 'category' => 'combo', 'rarity' => 'epic', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['size' => 20, 'weather' => 'rain']],
            ['name' => 'Winter Giant', 'icon' => '❄️', 'description' => 'Catch a 20"+ fish in winter', 'category' => 'combo', 'rarity' => 'epic', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['size' => 20, 'season' => 'winter']],
            ['name' => 'Night Owl Trophy', 'icon' => '🦉', 'description' => 'Catch a 20"+ fish after 9pm', 'category' => 'combo', 'rarity' => 'epic', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['size' => 20, 'time_after' => 21]],
            ['name' => 'New Spot Giant', 'icon' => '🆕', 'description' => 'Catch a 20"+ fish at a new location', 'category' => 'combo', 'rarity' => 'epic', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['size' => 20, 'new_location' => true]],
            ['name' => 'Double Digit Day', 'icon' => '🔥', 'description' => 'Catch 10+ fish including a 20"+ in one day', 'category' => 'combo', 'rarity' => 'epic', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['daily_count' => 10, 'size' => 20]],
            ['name' => 'Species Slam', 'icon' => '🎯', 'description' => 'Catch 3 species with 3 different flies in one day', 'category' => 'combo', 'rarity' => 'rare', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['daily_species' => 3, 'daily_flies' => 3]],
            ['name' => 'Location Hopper', 'icon' => '🦘', 'description' => 'Catch fish at 3 locations in one day', 'category' => 'combo', 'rarity' => 'rare', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['daily_locations' => 3]],
            ['name' => 'Perfect Conditions', 'icon' => '✨', 'description' => 'Catch 10+ fish during major solunar period', 'category' => 'combo', 'rarity' => 'rare', 'requirement_type' => 'combo', 'requirement_field' => 'combo', 'requirement_operator' => 'and', 'requirement_extra' => ['daily_count' => 10, 'solunar' => 'major']],
        ];
    }

    private function getMilestoneBadges(): array
    {
        return [
            ['name' => 'First Log', 'icon' => '📝', 'description' => 'Create your first fishing log', 'category' => 'milestone', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'log_count', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Getting Serious', 'icon' => '📊', 'description' => 'Create 10 fishing logs', 'category' => 'milestone', 'rarity' => 'common', 'requirement_type' => 'count', 'requirement_field' => 'log_count', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Dedicated Logger', 'icon' => '📈', 'description' => 'Create 50 fishing logs', 'category' => 'milestone', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'log_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Data Driven', 'icon' => '💾', 'description' => 'Create 100 fishing logs', 'category' => 'milestone', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'log_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Historian', 'icon' => '📚', 'description' => 'Create 500 fishing logs', 'category' => 'milestone', 'rarity' => 'epic', 'requirement_type' => 'count', 'requirement_field' => 'log_count', 'requirement_operator' => '>=', 'requirement_value' => 500],
            ['name' => 'Archivist', 'icon' => '🏛️', 'description' => 'Create 1000 fishing logs', 'category' => 'milestone', 'rarity' => 'legendary', 'requirement_type' => 'count', 'requirement_field' => 'log_count', 'requirement_operator' => '>=', 'requirement_value' => 1000],
            ['name' => 'First Year', 'icon' => '🎂', 'description' => 'Use the app for 1 year', 'category' => 'milestone', 'rarity' => 'rare', 'requirement_type' => 'account_age', 'requirement_field' => 'days_active', 'requirement_operator' => '>=', 'requirement_value' => 365],
            ['name' => 'Two Year Veteran', 'icon' => '🎖️', 'description' => 'Use the app for 2 years', 'category' => 'milestone', 'rarity' => 'epic', 'requirement_type' => 'account_age', 'requirement_field' => 'days_active', 'requirement_operator' => '>=', 'requirement_value' => 730],
            ['name' => 'Five Year Legend', 'icon' => '👑', 'description' => 'Use the app for 5 years', 'category' => 'milestone', 'rarity' => 'legendary', 'requirement_type' => 'account_age', 'requirement_field' => 'days_active', 'requirement_operator' => '>=', 'requirement_value' => 1825],
            ['name' => 'First Photo', 'icon' => '📸', 'description' => 'Add a photo to a fishing log', 'category' => 'milestone', 'rarity' => 'common', 'requirement_type' => 'exists', 'requirement_field' => 'photo_count', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Photographer', 'icon' => '📷', 'description' => 'Add 25 photos to fishing logs', 'category' => 'milestone', 'rarity' => 'uncommon', 'requirement_type' => 'count', 'requirement_field' => 'photo_count', 'requirement_operator' => '>=', 'requirement_value' => 25],
            ['name' => 'Photo Album', 'icon' => '🖼️', 'description' => 'Add 100 photos to fishing logs', 'category' => 'milestone', 'rarity' => 'rare', 'requirement_type' => 'count', 'requirement_field' => 'photo_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
        ];
    }

    private function getChallengeBadges(): array
    {
        return [
            ['name' => 'Minimalist', 'icon' => '🎯', 'description' => 'Catch 10 fish with only 1 fly pattern', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'single_fly_catches', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'One Rod Wonder', 'icon' => '🎣', 'description' => 'Catch 100 fish with the same rod', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'single_rod_catches', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Home Water Hero', 'icon' => '🏠', 'description' => 'Catch 100 fish at the same location', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'single_location_catches', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Species Focus', 'icon' => '🔍', 'description' => 'Catch 50 of the same species in a month', 'category' => 'challenge', 'rarity' => 'epic', 'requirement_type' => 'challenge', 'requirement_field' => 'monthly_species_max', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Perfect Week', 'icon' => '📅', 'description' => 'Catch fish every day for a week', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'weekly_catch_streak', 'requirement_operator' => '>=', 'requirement_value' => 7],
            ['name' => 'Perfect Month', 'icon' => '🗓️', 'description' => 'Catch fish every day for a month', 'category' => 'challenge', 'rarity' => 'legendary', 'requirement_type' => 'challenge', 'requirement_field' => 'monthly_catch_streak', 'requirement_operator' => '>=', 'requirement_value' => 30],
            ['name' => 'Size Progression', 'icon' => '📈', 'description' => 'Beat your personal best 3 times', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'pb_count', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Consistent Angler', 'icon' => '📊', 'description' => 'Log fish in 10 consecutive months', 'category' => 'challenge', 'rarity' => 'epic', 'requirement_type' => 'challenge', 'requirement_field' => 'consecutive_months', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Dawn to Dusk', 'icon' => '🌅', 'description' => 'Fish from sunrise to sunset in one day', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'full_day_fishing', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Marathon Angler', 'icon' => '🏃', 'description' => 'Log 8+ hours of fishing in one day', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_hours', 'requirement_operator' => '>=', 'requirement_value' => 8],
            ['name' => 'Comeback Kid', 'icon' => '💪', 'description' => 'Catch fish after 3 consecutive skunk days', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'comeback_after_skunk', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Underdog', 'icon' => '🐕', 'description' => 'Catch a 20"+ fish on a day you got skunked earlier', 'category' => 'challenge', 'rarity' => 'epic', 'requirement_type' => 'challenge', 'requirement_field' => 'trophy_after_skunk', 'requirement_operator' => '>=', 'requirement_value' => 1],
            ['name' => 'Early Riser', 'icon' => '⏰', 'description' => 'Start fishing before 5am', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'early_start', 'requirement_operator' => '<', 'requirement_value' => 5],
            ['name' => 'Night Fisher', 'icon' => '🌃', 'description' => 'Fish past midnight', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'late_fishing', 'requirement_operator' => '>=', 'requirement_value' => 24],
            ['name' => 'Weekend Warrior', 'icon' => '🎉', 'description' => 'Fish every weekend for a month', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'weekend_streak', 'requirement_operator' => '>=', 'requirement_value' => 4],
            ['name' => 'Weekday Warrior', 'icon' => '💼', 'description' => 'Fish on 5 consecutive weekdays', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'weekday_streak', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Multi-Species Day', 'icon' => '🐠', 'description' => 'Catch 4 different species in one day', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_species', 'requirement_operator' => '>=', 'requirement_value' => 4],
            ['name' => 'Quantity King', 'icon' => '👑', 'description' => 'Catch 100+ fish in a single day', 'category' => 'challenge', 'rarity' => 'legendary', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_quantity', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Triple Threat', 'icon' => '3️⃣', 'description' => 'Catch fish at 3 locations in one day', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_locations', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Fly Swap', 'icon' => '🔄', 'description' => 'Catch fish with 5 different flies in one day', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_flies', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Rod Rotation', 'icon' => '🔃', 'description' => 'Catch fish with 3 different rods in one day', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_rods', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Size Variety', 'icon' => '📐', 'description' => 'Catch fish ranging 10"+ in size difference in one day', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_size_range', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Persistence Pays', 'icon' => '💎', 'description' => 'Catch a fish after 5+ hours of fishing', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'hours_before_catch', 'requirement_operator' => '>=', 'requirement_value' => 5],
            ['name' => 'Quick Strike', 'icon' => '⚡', 'description' => 'Catch a fish within 15 minutes of starting', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'minutes_to_catch', 'requirement_operator' => '<=', 'requirement_value' => 15],
            ['name' => 'Double Header', 'icon' => '✌️', 'description' => 'Catch 2 fish over 20" in one day', 'category' => 'challenge', 'rarity' => 'epic', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_trophies', 'requirement_operator' => '>=', 'requirement_value' => 2],
            ['name' => 'Hat Trick', 'icon' => '🎩', 'description' => 'Catch 3 fish over 20" in one day', 'category' => 'challenge', 'rarity' => 'legendary', 'requirement_type' => 'challenge', 'requirement_field' => 'daily_trophies', 'requirement_operator' => '>=', 'requirement_value' => 3],
            ['name' => 'Skunked Survivor', 'icon' => '🦨', 'description' => 'Get skunked 10 times and keep fishing', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'skunk_count', 'requirement_operator' => '>=', 'requirement_value' => 10],
            ['name' => 'Tough Love', 'icon' => '💔', 'description' => 'Get skunked 50 times and keep fishing', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'skunk_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Never Give Up', 'icon' => '🏋️', 'description' => 'Get skunked 100 times and keep fishing', 'category' => 'challenge', 'rarity' => 'epic', 'requirement_type' => 'challenge', 'requirement_field' => 'skunk_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Catch and Release', 'icon' => '🔄', 'description' => 'Release 100 fish', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'released_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Conservation Hero', 'icon' => '🌿', 'description' => 'Release 500 fish', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'released_count', 'requirement_operator' => '>=', 'requirement_value' => 500],
            ['name' => 'Eco Warrior', 'icon' => '🌍', 'description' => 'Release 1000 fish', 'category' => 'challenge', 'rarity' => 'epic', 'requirement_type' => 'challenge', 'requirement_field' => 'released_count', 'requirement_operator' => '>=', 'requirement_value' => 1000],
            ['name' => 'Note Taker', 'icon' => '📝', 'description' => 'Add notes to 50 fishing logs', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'notes_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Detailed Logger', 'icon' => '📋', 'description' => 'Add notes to 200 fishing logs', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'notes_count', 'requirement_operator' => '>=', 'requirement_value' => 200],
            ['name' => 'Water Temp Tracker', 'icon' => '🌡️', 'description' => 'Log water temperature 50 times', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'water_temp_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Air Temp Tracker', 'icon' => '🌡️', 'description' => 'Log air temperature 50 times', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'air_temp_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Weather Watcher', 'icon' => '🌤️', 'description' => 'Log weather conditions 100 times', 'category' => 'challenge', 'rarity' => 'rare', 'requirement_type' => 'challenge', 'requirement_field' => 'weather_count', 'requirement_operator' => '>=', 'requirement_value' => 100],
            ['name' => 'Clarity Counter', 'icon' => '💧', 'description' => 'Log water clarity 50 times', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'clarity_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
            ['name' => 'Flow Tracker', 'icon' => '🌊', 'description' => 'Log water flow 50 times', 'category' => 'challenge', 'rarity' => 'uncommon', 'requirement_type' => 'challenge', 'requirement_field' => 'flow_count', 'requirement_operator' => '>=', 'requirement_value' => 50],
        ];
    }
}
