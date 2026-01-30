<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserWeather;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserWeather>
 */
class UserWeatherFactory extends Factory
{
    protected $model = UserWeather::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'temperature' => fake()->randomElement(['cold', 'cool', 'mild', 'warm', 'hot']),
            'cloud' => fake()->randomElement(['clear', 'partly cloudy', 'cloudy', 'overcast']),
            'wind' => fake()->randomElement(['calm', 'light', 'moderate', 'strong', 'gusty']),
            'precipitation' => fake()->randomElement(['none', 'light rain', 'rain', 'snow', 'drizzle']),
            'barometric_pressure' => fake()->randomElement(['rising', 'falling', 'stable', 'high', 'low']),
        ];
    }

    /**
     * Create weather for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create ideal fishing weather.
     */
    public function ideal(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => 'mild',
            'cloud' => 'partly cloudy',
            'wind' => 'light',
            'precipitation' => 'none',
            'barometric_pressure' => 'stable',
        ]);
    }
}

