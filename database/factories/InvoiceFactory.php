<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomElement([350000, 500000, 750000, 1500000]);

        return [
            'booking_id'     => Booking::factory(),
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'subtotal'       => $subtotal,
            'tax'            => 0,
            'total'          => $subtotal,
            'status'         => 'unpaid',
            'due_date'       => now()->addDays(7)->toDateString(),
            'paid_at'        => null,
            'notes'          => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'  => 'paid',
            'paid_at' => now()->toDateString(),
        ]);
    }
}
