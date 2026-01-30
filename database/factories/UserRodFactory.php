<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserRod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserRod>
 */
class UserRodFactory extends Factory
{
    protected $model = UserRod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['Orvis', 'Sage', 'Scott', 'Winston', 'Redington', 'Echo', 'TFO'];
        $models = ['Helios', 'X', 'Centric', 'Air', 'Hydrogen', 'Trout', 'Pro'];

        return [
            'user_id' => User::factory(),
            'rod_name' => fake()->randomElement($brands) . ' ' . fake()->randomElement($models),
            'rod_weight' => fake()->numberBetween(3, 8) . 'wt',
            'rod_length' => fake()->randomElement(["8'6\"", "9'", "9'6\"", "10'"]),
            'reel' => fake()->randomElement($brands) . ' ' . fake()->word(),
            'line' => fake()->randomElement(['WF', 'DT']) . fake()->numberBetween(3, 8) . 'F',
        ];
    }

    /**
     * Create a rod for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}

