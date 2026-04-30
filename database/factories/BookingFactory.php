<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'client_id'    => Client::factory(),
            'service_id'   => Service::factory(),
            'booking_date' => fake()->dateTimeBetween('now', '+30 days'),
            'duration'     => 90,
            'status'       => 'pending',
            'location'     => fake()->address(),
            'notes'        => fake()->optional()->sentence(),
            'price'        => 500000,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'confirmed']);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'cancelled']);
    }
}
