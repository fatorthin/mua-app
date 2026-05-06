<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookingCreate extends Component
{
    public string $client_id = '';
    public string $booking_date = '';
    public string $booking_time = '';
    public string $location = '';
    public string $notes = '';
    public bool $is_dp_paid = false;
    public string $dp_amount = '';

    // Multiple services
    public array $selectedServices = [
        ['service_id' => '', 'quantity' => 1, 'price' => ''],
    ];

    // For new client inline creation
    public bool $newClient = false;
    public string $new_client_name = '';
    public string $new_client_phone = '';

    protected function rules(): array
    {
        return [
            'client_id'    => $this->newClient ? 'nullable' : 'required|exists:clients,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required',
            'location'     => 'nullable|string|max:500',
            'notes'        => 'nullable|string|max:1000',
            'is_dp_paid'   => 'boolean',
            'dp_amount'    => $this->is_dp_paid ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'new_client_name'  => $this->newClient ? 'required|string|max:100' : 'nullable',
            'new_client_phone' => 'nullable|string|max:20',
            'selectedServices'             => 'required|array|min:1',
            'selectedServices.*.service_id' => 'required|exists:services,id',
            'selectedServices.*.quantity'   => 'required|integer|min:1',
            'selectedServices.*.price'      => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'client_id.required'    => 'Pilih klien.',
        'booking_date.required' => 'Tanggal booking wajib diisi.',
        'booking_date.after_or_equal' => 'Tanggal booking tidak boleh masa lalu.',
        'booking_time.required' => 'Jam booking wajib diisi.',
        'new_client_name.required' => 'Nama klien baru wajib diisi.',
        'dp_amount.required' => 'Nominal DP wajib diisi jika klien sudah membayar DP.',
        'dp_amount.numeric'  => 'Nominal DP harus berupa angka.',
        'dp_amount.min'      => 'Nominal DP tidak boleh negatif.',
        'selectedServices.required' => 'Tambahkan minimal satu layanan.',
        'selectedServices.min'      => 'Tambahkan minimal satu layanan.',
        'selectedServices.*.service_id.required' => 'Pilih layanan.',
        'selectedServices.*.quantity.required'    => 'Kuantitas wajib diisi.',
        'selectedServices.*.quantity.min'         => 'Kuantitas minimal 1.',
        'selectedServices.*.price.required'       => 'Harga wajib diisi.',
        'selectedServices.*.price.min'            => 'Harga tidak boleh negatif.',
    ];

    public function updatedSelectedServices(mixed $value, string $key): void
    {
        // When service_id changes, auto-fill the price from service
        if (str_ends_with($key, '.service_id') && $value) {
            $index   = explode('.', $key)[0];
            $service = Service::where('user_id', Auth::id())->find($value);
            if ($service) {
                $this->selectedServices[$index]['price'] = $service->price;
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

    private function normalizePhoneWith62(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        return $digits === '' ? null : '62' . $digits;
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        // Handle new client
        if ($this->newClient) {
            $client = Client::create([
                'user_id' => $user->id,
                'name'    => $this->new_client_name,
                'phone'   => $this->normalizePhoneWith62($this->new_client_phone),
            ]);
            $clientId = $client->id;
        } else {
            $clientId = $this->client_id;
        }

        $bookingDatetime = $this->booking_date . ' ' . $this->booking_time . ':00';

        // Compute totals and duration from selected services
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

        $dpAmount = $this->is_dp_paid ? (float) ($this->dp_amount !== '' ? $this->dp_amount : 0) : 0;
        if ($dpAmount > $totalPrice) {
            $this->addError('dp_amount', 'Nominal DP tidak boleh melebihi total biaya layanan.');
            return;
        }

        // Check for overlapping bookings
        $newStart = \Carbon\Carbon::parse($bookingDatetime);
        $newEnd   = $newStart->copy()->addMinutes($totalDuration);

        $existing = Booking::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['booking_date', 'duration']);

        $conflict = $existing->contains(function ($b) use ($newStart, $newEnd) {
            $existStart = \Carbon\Carbon::parse($b->booking_date);
            $existEnd   = $existStart->copy()->addMinutes($b->duration);
            return $newStart->lt($existEnd) && $newEnd->gt($existStart);
        });

        if ($conflict) {
            $this->addError('booking_date', 'Jadwal bentrok dengan booking lain. Pilih waktu berbeda.');
            return;
        }

        $primaryServiceId = $serviceItems[0]['service']->id;

        $booking = Booking::create([
            'user_id'      => $user->id,
            'client_id'    => $clientId,
            'service_id'   => $primaryServiceId,
            'booking_date' => $bookingDatetime,
            'duration'     => $totalDuration,
            'price'        => $totalPrice,
            'status'       => 'confirmed',
            'location'     => $this->location,
            'notes'        => $this->notes,
            'is_dp_paid'   => $this->is_dp_paid,
            'dp_amount'    => $dpAmount,
        ]);

        foreach ($serviceItems as $item) {
            BookingItem::create([
                'booking_id' => $booking->id,
                'service_id' => $item['service']->id,
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'duration'   => $item['service']->duration,
            ]);
        }

        // Auto-create invoice
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
        $invoiceTotal = max(0, $totalPrice - $dpAmount);
        $invoice = Invoice::create([
            'booking_id'     => $booking->id,
            'invoice_number' => $invoiceNumber,
            'subtotal'       => $totalPrice,
            'tax'            => 0,
            'total'          => $invoiceTotal,
            'status'         => 'unpaid',
            'due_date'       => now()->addDays(7)->toDateString(),
            'notes'          => $dpAmount > 0 ? 'DP terbayar: Rp ' . number_format($dpAmount, 0, ',', '.') : null,
        ]);

        // Do not block booking flow when WhatsApp gateway is unavailable.
        \App\Jobs\SendBookingInvoiceJob::dispatch($booking->load('client'), $invoice);

        session()->flash('success', 'Booking berhasil ditambahkan.');
        $this->redirect(route('bookings.index'), navigate: true);
    }

    public function render()
    {
        $clients  = Client::where('user_id', Auth::id())->orderBy('name')->get();
        $services = Service::where('user_id', Auth::id())->where('is_active', true)->orderBy('name')->get();

        return view('livewire.bookings.booking-create', compact('clients', 'services'));
    }
}
