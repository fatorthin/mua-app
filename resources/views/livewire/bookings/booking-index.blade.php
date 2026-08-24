<div>
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Search input --}}
            <div class="relative flex-1">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama klien..."
                    class="w-full pl-9 rounded-lg border-gray-300 text-sm focus:ring-pink-500 focus:border-pink-500 bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                {{-- Status Filter Dropdown --}}
                <select wire:model.live="statusFilter" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm focus:ring-pink-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="confirmed">Dikonfirmasi</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>

                {{-- Quick Date Range Dropdown --}}
                <select wire:model.live="quickDateFilter" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm focus:ring-pink-500 bg-white">
                    <option value="">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="tomorrow">Besok</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="upcoming">Mendatang</option>
                </select>

                {{-- Custom Date Picker --}}
                <div class="relative w-full sm:w-auto">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input wire:model.live="dateFilter" type="date"
                        class="w-full sm:w-auto pl-9 {{ $dateFilter ? 'pr-8' : 'pr-3' }} rounded-lg border-gray-300 text-sm focus:ring-pink-500 bg-white text-gray-700 font-medium">
                    @if ($dateFilter)
                        <button type="button" wire:click="$set('dateFilter', '')"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                            title="Hapus filter tanggal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Mode Kalender --}}
                <a href="{{ route('bookings.calendar') }}" wire:navigate
                    class="hidden sm:inline-flex items-center justify-center border border-pink-200 text-pink-600 px-3.5 py-2 rounded-lg text-sm font-medium hover:bg-pink-50 whitespace-nowrap text-center">
                    Kalender
                </a>

                {{-- Tambah Booking --}}
                <a href="{{ route('bookings.create') }}" wire:navigate
                    class="hidden sm:inline-flex items-center justify-center bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700 whitespace-nowrap text-center shadow-sm">
                    + Booking
                </a>
            </div>
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

        {{-- Mobile Load More Button --}}
        @if ($bookings->hasMorePages())
            <div class="p-4 md:hidden text-center border-t border-gray-100 bg-gray-50/50">
                <button wire:click="loadMore" wire:loading.attr="disabled"
                    class="w-full py-2.5 px-4 bg-white border border-pink-200 text-pink-600 font-semibold text-xs rounded-xl shadow-sm hover:bg-pink-50 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="loadMore">Muat Lebih Banyak (+15)</span>
                    <span wire:loading wire:target="loadMore" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Memuat data...
                    </span>
                </button>
            </div>
        @endif

        {{-- Desktop Pagination --}}
        @if ($bookings->hasPages())
            <div class="hidden md:block px-4 py-3 border-t border-gray-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
