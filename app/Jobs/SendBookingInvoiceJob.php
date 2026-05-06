<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingInvoiceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;
    public Invoice $invoice;

    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking, Invoice $invoice)
    {
        $this->booking = $booking;
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        $this->booking->loadMissing(['user', 'client', 'service']);

        $whatsAppService->sendInvoiceCreated($this->booking, $this->invoice);
    }
}
