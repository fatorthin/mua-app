<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => fake()->name(),
            'phone'   => fake()->phoneNumber(),
            'email'   => fake()->safeEmail(),
            'notes'   => fake()->optional()->sentence(),
        ];
    }
}
