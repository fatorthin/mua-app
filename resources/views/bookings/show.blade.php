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
                    <p>No. Invoice: <span
                            class="font-medium text-gray-800">{{ $booking->invoice->invoice_number }}</span></p>
                    <p>Status: <span class="font-medium text-gray-800">{{ $booking->invoice->status_label }}</span></p>
                    <p>Total Tagihan: <span class="font-medium text-gray-800">Rp
                            {{ number_format($booking->invoice->total, 0, ',', '.') }}</span></p>
                </div>
            @else
                <p class="text-sm text-gray-500">Invoice belum tersedia.</p>
            @endif

            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('bookings.edit', $booking) }}" wire:navigate
                    class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200">Edit
                    Booking</a>
                <a href="{{ route('bookings.index') }}" wire:navigate
                    class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700">Kembali ke
                    Daftar</a>
            </div>
        </div>
    </div>
</x-app-layout>
