<?php

use App\Http\Controllers\DeployWebhookController;
use App\Http\Controllers\InvoiceController;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::post('/webhooks/github/deploy', DeployWebhookController::class)
    ->name('webhooks.github.deploy');

Route::get('/invoices/{invoice}/public-pdf', [InvoiceController::class, 'publicPdf'])
    ->middleware('signed')
    ->name('invoices.public-pdf');
Route::get('/invoices/{invoice}/public-jpg', [InvoiceController::class, 'publicJpg'])
    ->middleware('signed')
    ->name('invoices.public-jpg');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // Bookings
    Route::get('/bookings', fn() => view('bookings.index'))->name('bookings.index');
    Route::get('/bookings/calendar', fn() => view('bookings.calendar'))->name('bookings.calendar');
    Route::get('/bookings/create', fn() => view('bookings.create'))->name('bookings.create');
    Route::get('/bookings/{booking}', function (Booking $booking) {
        abort_unless($booking->user_id === auth()->id(), 403);
        $booking->load(['client', 'service', 'items.service', 'invoice']);
        return view('bookings.show', compact('booking'));
    })->name('bookings.show');
    Route::get('/bookings/{booking}/edit', function (Booking $booking) {
        abort_unless($booking->user_id === auth()->id(), 403);
        return view('bookings.edit', compact('booking'));
    })->name('bookings.edit');

    // Clients
    Route::get('/clients', fn() => view('clients.index'))->name('clients.index');

    // Services
    Route::get('/services', fn() => view('services.index'))->name('services.index');

    // Invoices
    Route::get('/invoices', fn() => view('invoices.index'))->name('invoices.index');
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'previewJpg'])->name('invoices.preview');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    // Admin
    Route::get('/admin/users', fn() => view('admin.users'))->name('admin.users');
});

require __DIR__ . '/auth.php';
