<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'subtotal',
        'tax',
        'total',
        'status',
        'pdf_path',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax'      => 'decimal:2',
        'total'    => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public static function calculateDueDate(?\DateTimeInterface $bookingDate): string
    {
        if (! $bookingDate) {
            return now()->toDateString();
        }

        return \Carbon\Carbon::parse($bookingDate)->subDay()->toDateString();
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'unpaid' => 'Belum Dibayar',
            'paid'   => 'Sudah Dibayar',
            default  => $this->status,
        };
    }
}
