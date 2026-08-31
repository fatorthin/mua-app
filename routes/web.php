<?php

use App\Http\Controllers\DeployWebhookController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PricelistController;
use App\Http\Controllers\PublicPricelistController;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::post('/webhooks/github/deploy', DeployWebhookController::class)
    ->name('webhooks.github.deploy');

// Public Pricelists Microsite & Exports
Route::get('/p/{slug}', [PublicPricelistController::class, 'show'])->name('pricelists.public');
Route::get('/p/{slug}/pdf', [PublicPricelistController::class, 'pdf'])->name('pricelists.public-pdf');
Route::get('/p/{slug}/jpg', [PublicPricelistController::class, 'jpg'])->name('pricelists.public-jpg');

Route::get('/invoices/{invoice}/public-pdf', [InvoiceController::class, 'publicPdf'])
    ->middleware('signed')
    ->name('invoices.public-pdf');
Route::get('/invoices/{invoice}/public-preview', [InvoiceController::class, 'publicPreviewHtml'])
    ->middleware('signed')
    ->name('invoices.public-preview');
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
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'previewHtml'])->name('invoices.preview');
    Route::get('/invoices/{invoice}/download/{filename?}', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    // Pricelists
    Route::get('/pricelists', fn() => view('pricelists.index'))->name('pricelists.index');
    Route::get('/pricelists/create', fn() => view('pricelists.create'))->name('pricelists.create');
    Route::get('/pricelists/{pricelist}/edit', function (\App\Models\Pricelist $pricelist) {
        abort_unless($pricelist->user_id === auth()->id(), 403);
        return view('pricelists.edit', compact('pricelist'));
    })->name('pricelists.edit');
    Route::get('/pricelists/{pricelist}/pdf', [PricelistController::class, 'pdf'])->name('pricelists.pdf');
    Route::get('/pricelists/{pricelist}/jpg', [PricelistController::class, 'jpg'])->name('pricelists.jpg');

    // Admin
    Route::get('/admin/users', fn() => view('admin.users'))->name('admin.users');
});

require __DIR__ . '/auth.php';
