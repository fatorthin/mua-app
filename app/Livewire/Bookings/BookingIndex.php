<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class BookingIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $dateFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $booking = Booking::where('user_id', auth()->id())->findOrFail($id);
        $booking->invoice?->delete();
        $booking->delete();
        session()->flash('success', 'Booking berhasil dihapus.');
    }

    public function confirmBooking(int $id): void
    {
        Booking::where('user_id', auth()->id())->findOrFail($id)->update(['status' => 'confirmed']);
    }

    public function completeBooking(int $id): void
    {
        Booking::where('user_id', auth()->id())->findOrFail($id)->update(['status' => 'completed']);
    }

    public function render()
    {
        $bookings = Booking::with(['client', 'service'])
            ->where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->whereHas('client', fn($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFilter, fn($q) => $q->whereDate('booking_date', $this->dateFilter))
            ->orderByDesc('booking_date')
            ->paginate(15);

        return view('livewire.bookings.booking-index', compact('bookings'));
    }
}
