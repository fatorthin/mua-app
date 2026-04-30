<div>
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama klien..."
                class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-pink-500 focus:border-pink-500">

            <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu</option>
                <option value="confirmed">Dikonfirmasi</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>

            <input wire:model.live="dateFilter" type="date"
                class="rounded-lg border-gray-300 text-sm focus:ring-pink-500">

            <a href="{{ route('bookings.create') }}" wire:navigate
                class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700 whitespace-nowrap text-center">
                + Tambah Booking
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Klien</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Layanan</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal & Waktu</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Harga</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $booking->client->name }}</p>
                                <p class="text-xs text-gray-400">{{ $booking->client->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $booking->service->name }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $booking->booking_date->format('d M Y') }}<br>
                                <span class="text-xs text-gray-400">{{ $booking->booking_date->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $booking->formatted_price }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $booking->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $booking->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ $booking->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($booking->status === 'pending')
                                        <button wire:click="confirmBooking({{ $booking->id }})"
                                            class="text-xs text-blue-600 hover:underline">Konfirmasi</button>
                                    @endif
                                    @if ($booking->status === 'confirmed')
                                        <button wire:click="completeBooking({{ $booking->id }})"
                                            class="text-xs text-green-600 hover:underline">Selesai</button>
                                    @endif
                                    <a href="{{ route('bookings.edit', $booking) }}" wire:navigate
                                        class="text-xs text-gray-600 hover:underline">Edit</a>
                                    <button wire:click="delete({{ $booking->id }})" wire:confirm="Hapus booking ini?"
                                        class="text-xs text-red-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">Tidak ada booking ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
