<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserLocation>
 */
class UserLocationFactory extends Factory
{
    protected $model = UserLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true) . ' ' . fake()->randomElement(['River', 'Lake', 'Creek', 'Pond', 'Stream']),
            'water_type' => fake()->randomElement(['river', 'lake', 'stream', 'pond', 'ocean']),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country_id' => Country::factory(),
            'latitude' => fake()->latitude(25, 48), // US latitude range
            'longitude' => fake()->longitude(-125, -70), // US longitude range
        ];
    }

    /**
     * Create a location for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a Colorado location.
     */
    public function colorado(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'Denver',
            'state' => 'Colorado',
            'latitude' => 39.7392,
            'longitude' => -104.9903,
        ]);
    }
}

