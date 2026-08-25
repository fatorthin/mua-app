<?php

namespace App\Livewire\Invoices;

use App\Jobs\SendBookingInvoiceJob;
use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceIndex extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $periodMonth = '';
    public string $periodYear = '';

    // Payment modal state
    public bool $showPaymentModal = false;
    public ?int $payingInvoiceId = null;
    public ?Invoice $payingInvoice = null;
    public string $paymentMethod = 'Transfer Bank (BCA)';
    public string $paymentNotes = '';
    public bool $sendReceiptWa = true;

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPeriodMonth(): void
    {
        $this->resetPage();
    }

    public function updatingPeriodYear(): void
    {
        $this->resetPage();
    }

    public function openPaymentModal(int $id): void
    {
        $invoice = Invoice::with(['booking.client', 'booking.service'])
            ->whereHas('booking', fn($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($id);

        $this->payingInvoiceId   = $id;
        $this->payingInvoice     = $invoice;
        $this->paymentMethod     = 'Transfer Bank (BCA)';
        $this->paymentNotes      = '';
        $this->sendReceiptWa     = true;
        $this->showPaymentModal  = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->payingInvoiceId  = null;
        $this->payingInvoice    = null;
    }

    public function confirmPayment(WhatsAppService $whatsAppService): void
    {
        if (! $this->payingInvoiceId) {
            return;
        }

        $invoice = Invoice::with(['booking.client', 'booking.service', 'booking.user'])
            ->whereHas('booking', fn($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($this->payingInvoiceId);

        $paymentInfo = 'Lunas: ' . $this->paymentMethod;
        if (trim($this->paymentNotes) !== '') {
            $paymentInfo .= ' (' . trim($this->paymentNotes) . ')';
        }

        $existingNotes = $invoice->notes ? trim($invoice->notes) . ' | ' : '';
        $updatedNotes  = $existingNotes . $paymentInfo;

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now()->toDateString(),
            'notes'   => $updatedNotes,
        ]);

        if ($this->sendReceiptWa) {
            $whatsAppService->sendPaymentReceipt($invoice->booking, $invoice, $this->paymentMethod);
        }

        $this->closePaymentModal();
        session()->flash('success', 'Invoice #' . $invoice->invoice_number . ' berhasil dilunasi' . ($this->sendReceiptWa ? ' dan kuitansi WA telah dikirim ke klien.' : '.'));
    }

    public function markPaid(int $id, string $method = 'Transfer Bank'): void
    {
        $invoice = Invoice::whereHas('booking', fn($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($id);

        $notes = $invoice->notes ? trim($invoice->notes) . ' | Lunas: ' . $method : 'Lunas: ' . $method;

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now()->toDateString(),
            'notes'   => $notes,
        ]);

        session()->flash('success', 'Invoice #' . $invoice->invoice_number . ' berhasil ditandai Lunas.');
    }

    public function setUnpaid(int $id): void
    {
        $invoice = Invoice::whereHas('booking', fn($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($id);

        $invoice->update([
            'status'  => 'unpaid',
            'paid_at' => null,
        ]);

        session()->flash('success', 'Status invoice #' . $invoice->invoice_number . ' diubah menjadi Belum Dibayar.');
    }

    public function resendInvoice(int $id, WhatsAppService $whatsAppService): void
    {
        $invoice = Invoice::with(['booking.client', 'booking.user', 'booking.service', 'booking.items.service'])
            ->whereHas('booking', fn($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($id);

        $clientPhone = $invoice->booking->client?->phone;
        if (!$clientPhone) {
            session()->flash('error', 'Gagal: Nomor WhatsApp klien tidak ditemukan.');
            return;
        }

        $user = Auth::user();
        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $whatsAppService->gatewayConfigFor($user);

        if ($url === '' || $auth === '') {
            session()->flash('error', 'WhatsApp Gateway belum dikonfigurasi di sistem.');
            return;
        }

        if ($deviceId === '') {
            session()->flash('error', 'Perangkat WhatsApp belum terhubung. Silakan buka menu Profil dan lakukan Scan QR WhatsApp terlebih dahulu.');
            return;
        }

        try {
            $sent = $whatsAppService->sendInvoiceCreated($invoice->booking, $invoice);

            if ($sent) {
                session()->flash('success', 'Invoice #' . $invoice->invoice_number . ' berhasil dikirim ke WhatsApp ' . ($invoice->booking->client->name ?? 'klien') . '!');
            } else {
                session()->flash('error', 'Gagal mengirim pesan via WhatsApp Gateway. Pastikan status WhatsApp Anda di menu Profil sudah Connected / Scan QR.');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error resending invoice via WhatsApp: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengirim via WhatsApp Gateway: ' . $e->getMessage());
        }
    }

    public function exportCsv(): StreamedResponse
    {
        $user = Auth::user();
        $fileName = 'laporan-invoice-' . date('Ymd-His') . '.csv';

        $invoices = Invoice::with(['booking.client', 'booking.service'])
            ->whereHas('booking', fn($q) => $q->where('user_id', $user->id))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->periodMonth, fn($q) => $q->whereMonth('created_at', $this->periodMonth))
            ->when($this->periodYear, fn($q) => $q->whereYear('created_at', $this->periodYear))
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($invoices) {
            $file = fopen('php://output', 'w');
            // Add BOM for UTF-8 Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header
            fputcsv($file, [
                'No. Invoice',
                'Tanggal Terbit',
                'Nama Klien',
                'No. Telepon',
                'Layanan Utama',
                'Tanggal Booking',
                'Subtotal (Rp)',
                'DP (Rp)',
                'Total Tagihan (Rp)',
                'Status Pembayaran',
                'Tanggal Lunas',
                'Catatan / Info Pembayaran',
            ]);

            foreach ($invoices as $inv) {
                $dpAmount = ($inv->booking && $inv->booking->is_dp_paid) ? (float) $inv->booking->dp_amount : 0;
                fputcsv($file, [
                    $inv->invoice_number,
                    $inv->created_at ? $inv->created_at->format('d/m/Y') : '-',
                    $inv->booking?->client?->name ?? '-',
                    $inv->booking?->client?->phone ?? '-',
                    $inv->booking?->service?->name ?? '-',
                    $inv->booking?->booking_date ? $inv->booking->booking_date->format('d/m/Y H:i') : '-',
                    (float) $inv->subtotal,
                    $dpAmount,
                    (float) $inv->total,
                    $inv->status === 'paid' ? 'Lunas' : 'Belum Lunas',
                    $inv->paid_at ? $inv->paid_at->format('d/m/Y') : '-',
                    $inv->notes ?? '-',
                ]);
            }

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function render()
    {
        $user = Auth::user();

        $invoices = Invoice::with(['booking.client', 'booking.service'])
            ->whereHas('booking', fn($q) => $q->where('user_id', $user->id))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->periodMonth, fn($q) => $q->whereMonth('created_at', $this->periodMonth))
            ->when($this->periodYear, fn($q) => $q->whereYear('created_at', $this->periodYear))
            ->orderByDesc('created_at')
            ->paginate(15);

        // Stats summary for the active filter
        $summaryQuery = Invoice::whereHas('booking', fn($q) => $q->where('user_id', $user->id))
            ->when($this->periodMonth, fn($q) => $q->whereMonth('created_at', $this->periodMonth))
            ->when($this->periodYear, fn($q) => $q->whereYear('created_at', $this->periodYear));

        $stats = [
            'total_invoices' => (clone $summaryQuery)->count(),
            'total_paid'     => (clone $summaryQuery)->where('status', 'paid')->sum('total'),
            'total_unpaid'   => (clone $summaryQuery)->where('status', 'unpaid')->sum('total'),
        ];

        return view('livewire.invoices.invoice-index', compact('invoices', 'stats'));
    }
}
