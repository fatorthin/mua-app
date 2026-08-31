<?php

namespace Database\Factories;

use App\Models\Pricelist;
use App\Models\PricelistSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricelistSection>
 */
class PricelistSectionFactory extends Factory
{
    protected $model = PricelistSection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pricelist_id' => Pricelist::factory(),
            'name' => fake()->randomElement(['Wedding / Bridal Packages', 'Engagement & Pre-wedding', 'Graduation & Party Look', 'Add-on & Retouch Services']),
            'description' => fake()->sentence(),
            'order' => 0,
        ];
    }
}
