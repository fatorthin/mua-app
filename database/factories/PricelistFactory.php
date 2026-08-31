<?php

namespace Database\Factories;

use App\Models\Pricelist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pricelist>
 */
class PricelistFactory extends Factory
{
    protected $model = Pricelist::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true) . ' Pricelist';
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(6)),
            'description' => fake()->sentence(),
            'theme_template' => fake()->randomElement(['rose_blush', 'luxury_gold', 'clean_nude', 'sage_botanical']),
            'primary_color' => '#ec4899',
            'cover_image_path' => null,
            'terms_conditions' => [
                'DP 50% untuk lock tanggal (non-refundable).',
                'Pelunasan H-1 atau maksimal saat acara selesai.',
                'Biaya transport menyesuaikan lokasi di luar area studio.',
            ],
            'is_public' => true,
            'show_logo' => true,
            'show_social_media' => true,
            'show_contact_button' => true,
            'custom_footer_notes' => 'Terima kasih telah mempercayakan momen spesial Anda kepada kami.',
        ];
    }
}
