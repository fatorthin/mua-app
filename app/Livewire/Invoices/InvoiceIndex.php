<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updateStatus(int $id, string $status): void
    {
        abort_unless(in_array($status, ['unpaid', 'paid'], true), 400);

        $invoice = Invoice::whereHas('booking', fn($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($id);

        $invoice->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now()->toDateString() : null,
        ]);

        session()->flash('success', $status === 'paid'
            ? 'Status invoice berhasil diubah menjadi Sudah Dibayar.'
            : 'Status invoice berhasil diubah menjadi Belum Dibayar.');
    }

    public function render()
    {
        $invoices = Invoice::with(['booking.client', 'booking.service'])
            ->whereHas('booking', fn($q) => $q->where('user_id', auth()->id()))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.invoices.invoice-index', compact('invoices'));
    }
}
