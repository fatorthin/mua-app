<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookingEdit extends Component
{
    public Booking $booking;
    public string $client_id = '';
    public string $booking_date = '';
    public string $booking_time = '';
    public string $status = '';
    public string $location = '';
    public string $notes = '';
    public string $transport_fee = '';
    public bool $is_dp_paid = false;
    public string $dp_amount = '';

    // Multiple services
    public array $selectedServices = [];

    public function mount(Booking $booking): void
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        $this->booking       = $booking;
        $this->client_id     = (string) $booking->client_id;
        $this->booking_date  = $booking->booking_date->format('Y-m-d');
        $this->booking_time  = $booking->booking_date->format('H:i');
        $this->status        = $booking->status;
        $this->location      = $booking->location ?? '';
        $this->notes         = $booking->notes ?? '';
        $this->transport_fee = $booking->transport_fee ? (string) (float) $booking->transport_fee : '';
        $this->is_dp_paid    = (bool) $booking->is_dp_paid;
        $this->dp_amount     = $booking->dp_amount ? (string) (float) $booking->dp_amount : '';

        $items = $booking->items;
        if ($items->count() > 0) {
            $this->selectedServices = $items->map(function ($item) {
                return [
                    'service_id' => (string) $item->service_id,
                    'quantity'   => (int) $item->quantity,
                    'price'      => (string) (float) $item->price,
                ];
            })->toArray();
        } else {
            $this->selectedServices = [
                [
                    'service_id' => (string) $booking->service_id,
                    'quantity'   => 1,
                    'price'      => (string) (float) $booking->price,
                ],
            ];
        }
    }

    protected function rules(): array
    {
        return [
            'client_id'                      => 'required|exists:clients,id',
            'booking_date'                   => 'required|date',
            'booking_time'                   => 'required',
            'status'                         => 'required|in:pending,confirmed,completed,cancelled',
            'location'                       => 'nullable|string|max:500',
            'notes'                          => 'nullable|string|max:1000',
            'transport_fee'                  => 'nullable|numeric|min:0',
            'is_dp_paid'                     => 'boolean',
            'dp_amount'                      => $this->is_dp_paid ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'selectedServices'               => 'required|array|min:1',
            'selectedServices.*.service_id'  => 'required|exists:services,id',
            'selectedServices.*.quantity'    => 'required|integer|min:1',
            'selectedServices.*.price'       => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'client_id.required'                     => 'Pilih klien.',
        'booking_date.required'                  => 'Tanggal booking wajib diisi.',
        'booking_time.required'                  => 'Jam booking wajib diisi.',
        'dp_amount.required'                     => 'Nominal DP wajib diisi jika klien sudah membayar DP.',
        'dp_amount.numeric'                      => 'Nominal DP harus berupa angka.',
        'dp_amount.min'                          => 'Nominal DP tidak boleh negatif.',
        'selectedServices.required'              => 'Tambahkan minimal satu layanan.',
        'selectedServices.min'                   => 'Tambahkan minimal satu layanan.',
        'selectedServices.*.service_id.required' => 'Pilih layanan.',
        'selectedServices.*.quantity.required'   => 'Kuantitas wajib diisi.',
        'selectedServices.*.quantity.min'        => 'Kuantitas minimal 1.',
        'selectedServices.*.price.required'      => 'Harga wajib diisi.',
        'selectedServices.*.price.min'           => 'Harga tidak boleh negatif.',
    ];

    public function updatedSelectedServices(mixed $value, ?string $key = null): void
    {
        if ($key && str_ends_with($key, '.service_id') && $value) {
            $index = explode('.', $key)[0];
            $service = Service::where('user_id', Auth::id())->find($value);
            if ($service) {
                $this->selectedServices[$index]['price'] = (string) (float) $service->price;
            }
        }
    }

    public function addService(): void
    {
        $this->selectedServices[] = ['service_id' => '', 'quantity' => 1, 'price' => ''];
    }

    public function removeService(int $index): void
    {
        if (count($this->selectedServices) > 1) {
            array_splice($this->selectedServices, $index, 1);
            $this->selectedServices = array_values($this->selectedServices);
        }
    }

    public function updatedIsDpPaid(bool $value): void
    {
        if (! $value) {
            $this->dp_amount = '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $bookingDatetime = $this->booking_date . ' ' . $this->booking_time . ':00';

        $totalPrice    = 0;
        $totalDuration = 0;
        $serviceItems  = [];

        foreach ($this->selectedServices as $item) {
            $service = Service::where('user_id', $user->id)->findOrFail($item['service_id']);
            $qty     = (int) $item['quantity'];
            $price   = (float) $item['price'];
            $totalPrice    += $price * $qty;
            $totalDuration += $service->duration * $qty;
            $serviceItems[] = [
                'service'  => $service,
                'quantity' => $qty,
                'price'    => $price,
            ];
        }

        $transportFee = (float) ($this->transport_fee !== '' ? $this->transport_fee : 0);
        $grandTotal   = $totalPrice + $transportFee;

        $dpAmount = $this->is_dp_paid ? (float) ($this->dp_amount !== '' ? $this->dp_amount : 0) : 0;
        if ($dpAmount > $grandTotal) {
            $this->addError('dp_amount', 'Nominal DP tidak boleh melebihi total biaya booking (layanan + transport).');
            return;
        }

        // Check for schedule conflict excluding current booking
        $newStart = Carbon::parse($bookingDatetime);
        $newEnd   = $newStart->copy()->addMinutes($totalDuration);

        $existing = Booking::where('user_id', $user->id)
            ->where('id', '!=', $this->booking->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['booking_date', 'duration']);

        $conflict = $existing->contains(function ($b) use ($newStart, $newEnd) {
            $existStart = Carbon::parse($b->booking_date);
            $existEnd   = $existStart->copy()->addMinutes($b->duration);
            return $newStart->lt($existEnd) && $newEnd->gt($existStart);
        });

        if ($conflict) {
            $this->addError('booking_date', 'Jadwal bentrok dengan booking lain. Pilih waktu berbeda.');
            return;
        }

        $primaryServiceId = $serviceItems[0]['service']->id;

        $this->booking->update([
            'client_id'     => $this->client_id,
            'service_id'    => $primaryServiceId,
            'booking_date'  => $bookingDatetime,
            'duration'      => $totalDuration,
            'price'         => $grandTotal,
            'transport_fee' => $transportFee,
            'status'        => $this->status,
            'location'      => $this->location,
            'notes'         => $this->notes,
            'is_dp_paid'    => $this->is_dp_paid,
            'dp_amount'     => $dpAmount,
        ]);

        // Sync items
        $this->booking->items()->delete();
        foreach ($serviceItems as $item) {
            BookingItem::create([
                'booking_id' => $this->booking->id,
                'service_id' => $item['service']->id,
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'duration'   => $item['service']->duration,
            ]);
        }

        // Sync invoice if exists
        if ($this->booking->invoice) {
            $invoiceTotal = max(0, $grandTotal - $dpAmount);
            $this->booking->invoice->update([
                'subtotal' => $totalPrice,
                'tax'      => $transportFee,
                'total'    => $invoiceTotal,
                'due_date' => $this->booking->calculateInvoiceDueDate(),
                'notes'    => $dpAmount > 0 ? 'DP terbayar: Rp ' . number_format($dpAmount, 0, ',', '.') : null,
            ]);
        }

        session()->flash('success', 'Booking berhasil diperbarui.');
        $this->redirect(route('bookings.index'), navigate: true);
    }

    public function render()
    {
        $clients  = Client::where('user_id', Auth::id())->orderBy('name')->get();
        $services = Service::where('user_id', Auth::id())->where('is_active', true)->orderBy('name')->get();

        return view('livewire.bookings.booking-edit', compact('clients', 'services'));
    }
}
