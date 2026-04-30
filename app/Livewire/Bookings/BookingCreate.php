<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookingCreate extends Component
{
    public string $client_id = '';
    public string $service_id = '';
    public string $booking_date = '';
    public string $booking_time = '';
    public string $location = '';
    public string $notes = '';

    // For new client inline creation
    public bool $newClient = false;
    public string $new_client_name = '';
    public string $new_client_phone = '';
    public string $new_client_email = '';

    protected function rules(): array
    {
        return [
            'client_id'    => $this->newClient ? 'nullable' : 'required|exists:clients,id',
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required',
            'location'     => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:1000',
            'new_client_name'  => $this->newClient ? 'required|string|max:100' : 'nullable',
            'new_client_phone' => 'nullable|string|max:20',
            'new_client_email' => 'nullable|email|max:100',
        ];
    }

    protected $messages = [
        'client_id.required'    => 'Pilih klien.',
        'service_id.required'   => 'Pilih layanan.',
        'booking_date.required' => 'Tanggal booking wajib diisi.',
        'booking_date.after_or_equal' => 'Tanggal booking tidak boleh masa lalu.',
        'booking_time.required' => 'Jam booking wajib diisi.',
        'new_client_name.required' => 'Nama klien baru wajib diisi.',
    ];

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        // Handle new client
        if ($this->newClient) {
            $client = Client::create([
                'user_id' => $user->id,
                'name'    => $this->new_client_name,
                'phone'   => $this->new_client_phone,
                'email'   => $this->new_client_email,
            ]);
            $clientId = $client->id;
        } else {
            $clientId = $this->client_id;
        }

        $service = Service::where('user_id', $user->id)->findOrFail($this->service_id);
        $bookingDatetime = $this->booking_date . ' ' . $this->booking_time . ':00';

        // Check for overlapping bookings using Carbon (cross-database compatible)
        $newStart = \Carbon\Carbon::parse($bookingDatetime);
        $newEnd   = $newStart->copy()->addMinutes($service->duration);

        $existing = Booking::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['booking_date', 'duration']);

        $conflict = $existing->contains(function ($b) use ($newStart, $newEnd) {
            $existStart = \Carbon\Carbon::parse($b->booking_date);
            $existEnd   = $existStart->copy()->addMinutes($b->duration);
            // Two intervals overlap if: start1 < end2 AND end1 > start2
            return $newStart->lt($existEnd) && $newEnd->gt($existStart);
        });

        if ($conflict) {
            $this->addError('booking_date', 'Jadwal bentrok dengan booking lain. Pilih waktu berbeda.');
            return;
        }

        $booking = Booking::create([
            'user_id'      => $user->id,
            'client_id'    => $clientId,
            'service_id'   => $service->id,
            'booking_date' => $bookingDatetime,
            'duration'     => $service->duration,
            'price'        => $service->price,
            'status'       => 'pending',
            'location'     => $this->location,
            'notes'        => $this->notes,
        ]);

        // Auto-create invoice
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
        Invoice::create([
            'booking_id'     => $booking->id,
            'invoice_number' => $invoiceNumber,
            'subtotal'       => $service->price,
            'tax'            => 0,
            'total'          => $service->price,
            'status'         => 'unpaid',
            'due_date'       => now()->addDays(7)->toDateString(),
        ]);

        session()->flash('success', 'Booking berhasil ditambahkan.');
        $this->redirect(route('bookings.index'), navigate: true);
    }

    public function render()
    {
        $clients  = Client::where('user_id', auth()->id())->orderBy('name')->get();
        $services = Service::where('user_id', auth()->id())->where('is_active', true)->orderBy('name')->get();

        return view('livewire.bookings.booking-create', compact('clients', 'services'));
    }
}
