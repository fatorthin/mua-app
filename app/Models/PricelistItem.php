<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricelistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pricelist_section_id',
        'service_id',
        'name',
        'price',
        'duration_text',
        'description',
        'features',
        'is_highlighted',
        'order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_highlighted' => 'boolean',
        'price' => 'decimal:2',
        'order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(PricelistSection::class, 'pricelist_section_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
