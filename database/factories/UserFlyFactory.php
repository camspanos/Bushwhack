<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserFly;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserFly>
 */
class UserFlyFactory extends Factory
{
    protected $model = UserFly::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $flyNames = [
            'Adams', 'Elk Hair Caddis', 'Woolly Bugger', 'Pheasant Tail',
            'Hare\'s Ear', 'Royal Wulff', 'Parachute Adams', 'Stimulator',
            'Copper John', 'Prince Nymph', 'San Juan Worm', 'Zebra Midge',
        ];

        $colors = ['olive', 'brown', 'black', 'tan', 'white', 'red', 'orange', 'yellow'];
        $types = ['dry', 'nymph', 'streamer', 'wet', 'emerger'];

        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement($flyNames),
            'color' => fake()->randomElement($colors),
            'size' => fake()->numberBetween(10, 22),
            'type' => fake()->randomElement($types),
        ];
    }

    /**
     * Create a fly for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a dry fly.
     */
    public function dry(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'dry',
        ]);
    }

    /**
     * Create a nymph.
     */
    public function nymph(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'nymph',
        ]);
    }

    /**
     * Create a streamer.
     */
    public function streamer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'streamer',
        ]);
    }
}

