<?php

use App\Jobs\SendBookingReminderJob;
use App\Models\Booking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

$reminderTimezone = 'Asia/Jakarta';

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim reminder WA H-1 ke semua klien yang bookingnya besok
Schedule::call(function () use ($reminderTimezone) {
    $tomorrow = now($reminderTimezone)->addDay()->toDateString();

    $bookings = Booking::with(['client', 'service'])
        ->whereDate('booking_date', $tomorrow)
        ->whereIn('status', ['confirmed', 'pending'])
        ->get();

    $bookings->each(function (Booking $booking) {
        SendBookingReminderJob::dispatch($booking);
    });

    Log::info('Dispatched booking reminders.', [
        'booking_ids' => $bookings->pluck('id')->all(),
        'target_date' => $tomorrow,
        'timezone' => $reminderTimezone,
    ]);
})->dailyAt('08:00')->timezone($reminderTimezone)->name('booking-reminders')->withoutOverlapping();
