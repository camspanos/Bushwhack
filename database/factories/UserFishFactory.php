<?php

namespace Database\Factories;

use App\Models\FishSpecies;
use App\Models\User;
use App\Models\UserFish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserFish>
 */
class UserFishFactory extends Factory
{
    protected $model = UserFish::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $species = [
            'Rainbow Trout', 'Brown Trout', 'Brook Trout', 'Cutthroat Trout',
            'Largemouth Bass', 'Smallmouth Bass', 'Bluegill', 'Crappie',
        ];

        return [
            'user_id' => User::factory(),
            'species' => fake()->randomElement($species),
            'water_type' => fake()->randomElement(['freshwater', 'saltwater']),
            'fish_species_id' => null,
        ];
    }

    /**
     * Create a fish for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Link to a global fish species.
     */
    public function withFishSpecies(FishSpecies $fishSpecies): static
    {
        return $this->state(fn (array $attributes) => [
            'fish_species_id' => $fishSpecies->id,
            'species' => $fishSpecies->species,
            'water_type' => $fishSpecies->water_type,
        ]);
    }
}

