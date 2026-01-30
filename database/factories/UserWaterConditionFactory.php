<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserWaterCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserWaterCondition>
 */
class UserWaterConditionFactory extends Factory
{
    protected $model = UserWaterCondition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'temperature' => fake()->randomElement(['cold', 'cool', 'moderate', 'warm']),
            'clarity' => fake()->randomElement(['clear', 'slightly murky', 'murky', 'muddy']),
            'level' => fake()->randomElement(['low', 'normal', 'high', 'flood']),
            'speed' => fake()->randomElement(['still', 'slow', 'moderate', 'fast', 'rapid']),
            'surface_condition' => fake()->randomElement(['calm', 'rippled', 'choppy', 'rough']),
            'tide' => fake()->optional()->randomElement(['incoming', 'outgoing', 'high', 'low', 'slack']),
        ];
    }

    /**
     * Create water condition for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create ideal fishing water conditions.
     */
    public function ideal(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => 'moderate',
            'clarity' => 'clear',
            'level' => 'normal',
            'speed' => 'moderate',
            'surface_condition' => 'rippled',
            'tide' => null,
        ]);
    }
}

