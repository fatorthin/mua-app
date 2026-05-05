<?php

use App\Jobs\SendBookingReminderJob;
use App\Models\Booking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim reminder WA H-1 ke semua klien yang bookingnya besok
Schedule::call(function () {
    $tomorrow = now()->addDay()->toDateString();

    Booking::with(['client', 'service'])
        ->whereDate('booking_date', $tomorrow)
        ->whereIn('status', ['confirmed', 'pending'])
        ->each(function (Booking $booking) {
            SendBookingReminderJob::dispatch($booking);
        });
})->dailyAt('08:00')->name('booking-reminders')->withoutOverlapping();
