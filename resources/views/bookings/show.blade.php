<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('bookings.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600">Booking</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-800 font-semibold">Detail Booking</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Detail Booking</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $booking->booking_date->format('d M Y H:i') }}</p>
                </div>
                <span
                    class="px-3 py-1 rounded-full text-xs font-medium w-max
                    {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $booking->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $booking->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ $booking->status_label }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5 text-sm">
                <div class="rounded-lg border border-gray-100 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Klien</p>
                    <p class="font-medium text-gray-800">{{ $booking->client->name }}</p>
                    <p class="text-gray-500">{{ $booking->client->phone ?: '-' }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Lokasi</p>
                    <p class="font-medium text-gray-800 break-all">{{ $booking->location ?: '-' }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Durasi Total</p>
                    <p class="font-medium text-gray-800">{{ $booking->duration }} menit</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Total Harga</p>
                    <p class="font-semibold text-gray-800">{{ $booking->formatted_price }}</p>
                </div>
                @if ($booking->transport_fee > 0)
                    <div class="rounded-lg border border-blue-100 p-4 bg-blue-50">
                        <p class="text-xs uppercase tracking-wider text-blue-700 mb-1">Biaya Transport</p>
                        <p class="font-semibold text-blue-800">Rp
                            {{ number_format($booking->transport_fee, 0, ',', '.') }}</p>
                    </div>
                @endif
                @if ($booking->is_dp_paid)
                    <div class="rounded-lg border border-green-100 p-4 bg-green-50">
                        <p class="text-xs uppercase tracking-wider text-green-700 mb-1">DP Dibayar</p>
                        <p class="font-semibold text-green-800">Rp
                            {{ number_format($booking->dp_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-orange-100 p-4 bg-orange-50">
                        <p class="text-xs uppercase tracking-wider text-orange-700 mb-1">Sisa Pembayaran</p>
                        <p class="font-semibold text-orange-800">Rp
                            {{ number_format($booking->price - $booking->dp_amount, 0, ',', '.') }}</p>
                    </div>
                @endif
            </div>

            @if ($booking->notes)
                <div class="mt-4 rounded-lg border border-gray-100 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Catatan</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-800">Rincian Layanan</h4>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Layanan</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Qty</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Durasi</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-600">Harga</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-600">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($booking->items as $item)
                            <tr>
                                <td class="px-6 py-3 text-gray-800 font-medium">{{ $item->service?->name ?? '-' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $item->duration }} menit</td>
                                <td class="px-6 py-3 text-right text-gray-600">Rp
                                    {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right font-medium text-gray-800">Rp
                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">Belum ada rincian
                                    layanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-gray-100">
                @forelse($booking->items as $item)
                    <div class="p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-gray-800">{{ $item->service?->name ?? '-' }}</p>
                            <p class="text-sm font-semibold text-gray-800">Rp
                                {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</p>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs text-gray-600">
                            <div>
                                <p class="text-gray-400">Qty</p>
                                <p class="font-medium text-gray-700">{{ $item->quantity }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">Durasi</p>
                                <p class="font-medium text-gray-700">{{ $item->duration }} menit</p>
                            </div>
                            <div>
                                <p class="text-gray-400">Harga</p>
                                <p class="font-medium text-gray-700">Rp {{ number_format($item->price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-sm">Belum ada rincian layanan.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Invoice</h4>
            @if ($booking->invoice)
                <div class="text-sm text-gray-600 space-y-1">
                    <p>No. Invoice: <span class="font-medium text-gray-800">{{ $booking->invoice->invoice_number }}</span></p>
                    <p>Status: <span class="font-medium text-gray-800">{{ $booking->invoice->status_label }}</span></p>
                    <p>Total Tagihan: <span class="font-medium text-gray-800">Rp
                            {{ number_format($booking->invoice->total, 0, ',', '.') }}</span></p>
                </div>
            @else
                <p class="text-sm text-gray-500">Invoice belum tersedia.</p>
            @endif

            <div class="mt-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('bookings.edit', $booking) }}" wire:navigate class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                    Edit Booking
                </a>

                @if ($booking->invoice)
                    <button type="button"
                        @click="$dispatch('open-invoice-preview', { id: {{ $booking->invoice->id }}, number: '{{ $booking->invoice->invoice_number }}', previewUrl: '{{ route('invoices.preview', $booking->invoice, false) }}', downloadUrl: '{{ route('invoices.download', $booking->invoice, false) }}' })"
                        class="inline-flex items-center gap-1.5 bg-pink-50 text-pink-700 border border-pink-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat Invoice
                    </button>
                    <a href="{{ route('invoices.download', $booking->invoice, false) }}" download class="inline-flex items-center gap-1.5 bg-gray-50 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download PDF
                    </a>
                @endif

                @if ($booking->client && $booking->client->phone)
                    @php
                        $cleanPhone = preg_replace('/\D+/', '', $booking->client->phone);
                        $waHref = 'https://wa.me/' . $cleanPhone;
                    @endphp
                    <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        Chat Klien via WhatsApp
                    </a>
                @endif

                @php
                    $gcStart = $booking->booking_date->format('Ymd\THis');
                    $gcEnd = $booking->booking_date->clone()->addMinutes($booking->duration)->format('Ymd\THis');
                    $gcTitle = urlencode('MUA Booking – ' . ($booking->client->name ?? 'Klien'));
                    $gcDetails = urlencode('Layanan: ' . ($booking->service?->name ?? '-') . "\n" . 'Durasi: ' . $booking->duration . ' menit' . "\n" . 'Catatan: ' . ($booking->notes ?: '-'));
                    $gcLocation = urlencode($booking->location ?: '');
                    $gcUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE' . "&text={$gcTitle}" . "&dates={$gcStart}/{$gcEnd}" . "&details={$gcDetails}" . "&location={$gcLocation}";
                @endphp
                <a href="{{ $gcUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5zm2 4h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2zM7 15h2v2H7zm4 0h2v2h-2z" />
                    </svg>
                    Tambah ke Google Calendar
                </a>

                <a href="{{ route('bookings.index') }}" wire:navigate class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700 transition-colors">
                    Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
