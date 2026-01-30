<?php

namespace Database\Factories;

use App\Models\FishingLog;
use App\Models\User;
use App\Models\UserFish;
use App\Models\UserFly;
use App\Models\UserLocation;
use App\Models\UserRod;
use App\Models\UserWaterCondition;
use App\Models\UserWeather;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FishingLog>
 */
class FishingLogFactory extends Factory
{
    protected $model = FishingLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $moonPhases = ['New Moon', 'Waxing Crescent', 'First Quarter', 'Waxing Gibbous', 'Full Moon', 'Waning Gibbous', 'Last Quarter', 'Waning Crescent'];
        $timeOfDay = ['Dawn', 'Morning', 'Midday', 'Afternoon', 'Dusk', 'Night'];
        $styles = ['dry fly', 'nymph', 'streamer', 'wet fly', 'euro nymph'];

        return [
            'user_id' => User::factory(),
            'user_location_id' => null,
            'user_rod_id' => null,
            'user_fish_id' => null,
            'user_fly_id' => null,
            'user_weather_id' => null,
            'user_water_condition_id' => null,
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'time' => fake()->time('H:i'),
            'time_of_day' => fake()->randomElement($timeOfDay),
            'quantity' => fake()->numberBetween(0, 20),
            'max_size' => fake()->optional(0.7)->randomFloat(2, 6, 30),
            'style' => fake()->randomElement($styles),
            'moon_phase' => fake()->randomElement($moonPhases),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Create a fishing log for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a fishing log with all related data.
     */
    public function withAllRelations(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
                'user_location_id' => UserLocation::factory()->forUser($user)->create()->id,
                'user_rod_id' => UserRod::factory()->forUser($user)->create()->id,
                'user_fish_id' => UserFish::factory()->forUser($user)->create()->id,
                'user_fly_id' => UserFly::factory()->forUser($user)->create()->id,
            ];
        });
    }

    /**
     * Create a successful fishing log (caught fish).
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(1, 20),
            'max_size' => fake()->randomFloat(2, 8, 24),
        ]);
    }

    /**
     * Create a skunked fishing log (no fish caught).
     */
    public function skunked(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
            'max_size' => null,
        ]);
    }

    /**
     * Create a fishing log for a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Create a fishing log for the current year.
     */
    public function thisYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween(now()->startOfYear(), 'now'),
        ]);
    }

    /**
     * Create a fishing log for a specific year.
     */
    public function inYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween("$year-01-01", "$year-12-31"),
        ]);
    }
}

