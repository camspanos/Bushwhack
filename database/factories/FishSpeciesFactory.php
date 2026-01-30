<?php

namespace Database\Factories;

use App\Models\FishSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FishSpecies>
 */
class FishSpeciesFactory extends Factory
{
    protected $model = FishSpecies::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $species = [
            'Rainbow Trout', 'Brown Trout', 'Brook Trout', 'Cutthroat Trout',
            'Largemouth Bass', 'Smallmouth Bass', 'Striped Bass',
            'Bluegill', 'Crappie', 'Walleye', 'Northern Pike',
            'Salmon', 'Steelhead', 'Carp', 'Catfish',
        ];

        return [
            'species' => fake()->randomElement($species),
            'water_type' => fake()->randomElement(['freshwater', 'saltwater']),
        ];
    }

    /**
     * Create a freshwater species.
     */
    public function freshwater(): static
    {
        return $this->state(fn (array $attributes) => [
            'water_type' => 'freshwater',
        ]);
    }

    /**
     * Create a saltwater species.
     */
    public function saltwater(): static
    {
        return $this->state(fn (array $attributes) => [
            'water_type' => 'saltwater',
        ]);
    }
}

