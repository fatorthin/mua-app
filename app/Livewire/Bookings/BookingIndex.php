<?php

namespace App\Livewire\Bookings;

use App\Jobs\SendBookingReminderJob;
use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class BookingIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $dateFilter = '';
    public string $quickDateFilter = '';
    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->quickDateFilter = '';
        $this->resetPage();
    }

    public function updatingQuickDateFilter(): void
    {
        $this->dateFilter = '';
        $this->resetPage();
    }

    public function setQuickDate(string $val): void
    {
        $this->quickDateFilter = $val;
        $this->dateFilter = '';
        $this->resetPage();
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
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

    public function sendReminderNow(int $id): void
    {
        $booking = Booking::with(['client', 'service', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        SendBookingReminderJob::dispatch($booking);

        session()->flash('success', 'Pengingat WhatsApp berhasil dijadwalkan untuk dikirim ke ' . ($booking->client?->name ?? 'klien') . '.');
    }

    public function render()
    {
        $bookings = Booking::with(['client', 'service'])
            ->where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->whereHas('client', fn($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFilter, fn($q) => $q->whereDate('booking_date', $this->dateFilter))
            ->when(!$this->dateFilter && $this->quickDateFilter, function ($q) {
                match ($this->quickDateFilter) {
                    'today'      => $q->whereDate('booking_date', now()->toDateString()),
                    'tomorrow'   => $q->whereDate('booking_date', now()->addDay()->toDateString()),
                    'this_week'  => $q->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()]),
                    'this_month' => $q->whereBetween('booking_date', [now()->startOfMonth(), now()->endOfMonth()]),
                    'upcoming'   => $q->where('booking_date', '>=', now()->toDateString()),
                    default      => null,
                };
            })
            ->orderByDesc('booking_date')
            ->paginate($this->perPage);

        return view('livewire.bookings.booking-index', compact('bookings'));
    }
}
