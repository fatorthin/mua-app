<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'price',
        'starts_at',
        'expires_at',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'starts_at'  => 'date',
        'expires_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function getPlanLabelAttribute(): string
    {
        return match ($this->plan) {
            'free'  => 'Gratis',
            'basic' => 'Basic',
            'pro'   => 'Pro',
            default => $this->plan,
        };
    }
}
