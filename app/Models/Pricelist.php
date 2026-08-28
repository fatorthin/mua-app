<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Pricelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'theme_template',
        'primary_color',
        'cover_image_path',
        'terms_conditions',
        'is_public',
        'show_logo',
        'show_social_media',
        'show_contact_button',
        'custom_footer_notes',
    ];

    protected $casts = [
        'terms_conditions' => 'array',
        'is_public' => 'boolean',
        'show_logo' => 'boolean',
        'show_social_media' => 'boolean',
        'show_contact_button' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($pricelist) {
            if (empty($pricelist->slug)) {
                $base = Str::slug($pricelist->title ?: 'pricelist');
                $random = Str::lower(Str::random(6));
                $pricelist->slug = "{$base}-{$random}";
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PricelistSection::class)->orderBy('order', 'asc');
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(PricelistItem::class, PricelistSection::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return url("/p/{$this->slug}");
    }
}
