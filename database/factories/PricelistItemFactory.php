<?php

namespace Database\Factories;

use App\Models\PricelistItem;
use App\Models\PricelistSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricelistItem>
 */
class PricelistItemFactory extends Factory
{
    protected $model = PricelistItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pricelist_section_id' => PricelistSection::factory(),
            'service_id' => null,
            'name' => fake()->words(3, true),
            'price' => fake()->numberBetween(350000, 3500000),
            'duration_text' => '2-3 Jam',
            'description' => fake()->sentence(),
            'features' => [
                'Free Softlens Normal/Minus',
                'Include 1x Retouch Makeup',
                'Hijabdo / Hairdo Styling',
                '1x Pasang Fake Eyelashes Premium',
            ],
            'is_highlighted' => fake()->boolean(30),
            'order' => 0,
        ];
    }
}
