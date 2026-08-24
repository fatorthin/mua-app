<div>
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5 space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama klien..."
                    class="w-full pl-9 rounded-lg border-gray-300 text-sm focus:ring-pink-500 focus:border-pink-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div x-data="{ hasVal: @entangle('dateFilter') !== '' }" class="relative w-full sm:w-auto">
                <input wire:model.live="dateFilter" @change="hasVal = $event.target.value !== ''" type="date"
                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500 sm:w-auto">
                <span x-show="!hasVal"
                    class="pointer-events-none absolute inset-0 flex items-center rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-400">
                    Filter Tanggal
                </span>
            </div>

            <a href="{{ route('bookings.calendar') }}" wire:navigate
                class="hidden sm:inline-flex items-center justify-center border border-pink-200 text-pink-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-50 whitespace-nowrap text-center">
                Mode Kalender
            </a>

            <a href="{{ route('bookings.create') }}" wire:navigate
                class="hidden sm:inline-flex items-center justify-center bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700 whitespace-nowrap text-center shadow-sm">
                + Tambah Booking
            </a>
        </div>

        {{-- Mobile & Desktop Horizontal Filter Chips --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 -mx-1 px-1 text-xs no-scrollbar">
            <button type="button" wire:click="$set('statusFilter', '')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === '' ? 'bg-pink-600 text-white shadow-sm font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua Status
            </button>
            <button type="button" wire:click="$set('statusFilter', 'pending')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === 'pending' ? 'bg-yellow-500 text-white shadow-sm font-semibold' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' }}">
                Menunggu (Pending)
            </button>
            <button type="button" wire:click="$set('statusFilter', 'confirmed')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === 'confirmed' ? 'bg-blue-600 text-white shadow-sm font-semibold' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                Dikonfirmasi
            </button>
            <button type="button" wire:click="$set('statusFilter', 'completed')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === 'completed' ? 'bg-green-600 text-white shadow-sm font-semibold' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                Selesai
            </button>
            <button type="button" wire:click="$set('statusFilter', 'cancelled')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === 'cancelled' ? 'bg-red-600 text-white shadow-sm font-semibold' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                Dibatalkan
            </button>
        </div>
    </div>

    {{-- Table / Card View --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
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
                                <div class="flex items-center justify-end gap-2.5">
                                    <a href="{{ route('bookings.show', $booking) }}" wire:navigate
                                        class="text-xs text-pink-600 hover:underline font-medium">Detail</a>
                                    @if ($booking->status === 'pending')
                                        <button wire:click="confirmBooking({{ $booking->id }})"
                                            wire:confirm="Konfirmasi booking ini?"
                                            class="text-xs text-blue-600 hover:underline">Konfirmasi</button>
                                    @endif
                                    @if ($booking->status === 'confirmed')
                                        <button wire:click="completeBooking({{ $booking->id }})"
                                            wire:confirm="Tandai booking ini sebagai selesai?"
                                            class="text-xs text-green-600 hover:underline">Selesai</button>
                                    @endif
                                    <button wire:click="sendReminderNow({{ $booking->id }})"
                                        wire:confirm="Kirim pengingat WhatsApp ke {{ $booking->client?->name ?? 'klien' }} sekarang?"
                                        class="text-xs text-emerald-600 hover:underline" title="Kirim Pengingat WA">Reminder WA</button>
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

        {{-- Mobile Card View --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($bookings as $booking)
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-800">{{ $booking->client->name }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->client->phone }}</p>
                        </div>
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-medium shrink-0
                            {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $booking->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $booking->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $booking->status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-xs text-gray-400">Layanan</p>
                            <p class="text-gray-700 truncate">{{ $booking->service->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Harga</p>
                            <p class="text-gray-700 font-medium">{{ $booking->formatted_price }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-400">Waktu</p>
                            <p class="text-gray-700">{{ $booking->booking_date->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3 pt-2 border-t border-gray-50">
                        <a href="{{ route('bookings.show', $booking) }}" wire:navigate
                            class="text-sm font-medium text-pink-600 hover:text-pink-700">Detail</a>
                        @if ($booking->status === 'pending')
                            <button wire:click="confirmBooking({{ $booking->id }})"
                                wire:confirm="Konfirmasi booking ini?"
                                class="text-sm font-medium text-blue-600 hover:text-blue-700">Konfirmasi</button>
                        @endif
                        @if ($booking->status === 'confirmed')
                            <button wire:click="completeBooking({{ $booking->id }})"
                                wire:confirm="Tandai booking ini sebagai selesai?"
                                class="text-sm font-medium text-green-600 hover:text-green-700">Selesai</button>
                        @endif
                        <button wire:click="sendReminderNow({{ $booking->id }})"
                            wire:confirm="Kirim pengingat WhatsApp ke {{ $booking->client?->name ?? 'klien' }} sekarang?"
                            class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Reminder WA</button>
                        <a href="{{ route('bookings.edit', $booking) }}" wire:navigate
                            class="text-sm font-medium text-gray-600 hover:text-gray-700">Edit</a>
                        <button wire:click="delete({{ $booking->id }})" wire:confirm="Hapus booking ini?"
                            class="text-sm font-medium text-red-500 hover:text-red-600">Hapus</button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    Tidak ada booking ditemukan.
                </div>
            @endforelse
        </div>

        @if ($bookings->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
