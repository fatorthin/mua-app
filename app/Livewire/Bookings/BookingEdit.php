<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use Livewire\Component;

class BookingEdit extends Component
{
    public Booking $booking;
    public string $client_id = '';
    public string $service_id = '';
    public string $booking_date = '';
    public string $booking_time = '';
    public string $status = '';
    public string $location = '';
    public string $notes = '';

    public function mount(Booking $booking): void
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        $this->booking      = $booking;
        $this->client_id    = (string) $booking->client_id;
        $this->service_id   = (string) $booking->service_id;
        $this->booking_date = $booking->booking_date->format('Y-m-d');
        $this->booking_time = $booking->booking_date->format('H:i');
        $this->status       = $booking->status;
        $this->location     = $booking->location ?? '';
        $this->notes        = $booking->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'client_id'    => 'required|exists:clients,id',
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
            'location'     => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $service = Service::findOrFail($this->service_id);

        $this->booking->update([
            'client_id'    => $this->client_id,
            'service_id'   => $this->service_id,
            'booking_date' => $this->booking_date . ' ' . $this->booking_time . ':00',
            'duration'     => $service->duration,
            'price'        => $service->price,
            'status'       => $this->status,
            'location'     => $this->location,
            'notes'        => $this->notes,
        ]);

        session()->flash('success', 'Booking berhasil diperbarui.');
        $this->redirect(route('bookings.index'), navigate: true);
    }

    public function render()
    {
        $clients  = Client::where('user_id', auth()->id())->orderBy('name')->get();
        $services = Service::where('user_id', auth()->id())->where('is_active', true)->orderBy('name')->get();

        return view('livewire.bookings.booking-edit', compact('clients', 'services'));
    }
}
