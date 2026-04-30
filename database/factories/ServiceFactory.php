<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => fake()->randomElement(['Bridal Makeup', 'Party Makeup', 'Wisuda', 'Photoshoot']),
            'description' => fake()->sentence(),
            'price'       => fake()->randomElement([350000, 500000, 750000, 1500000]),
            'duration'    => fake()->randomElement([60, 90, 120, 180]),
            'is_active'   => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => ['is_active' => false]);
    }
}
