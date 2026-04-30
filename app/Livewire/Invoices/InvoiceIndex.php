<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function markPaid(int $id): void
    {
        $invoice = Invoice::whereHas('booking', fn($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($id);
        $invoice->update(['status' => 'paid', 'paid_at' => now()->toDateString()]);
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
