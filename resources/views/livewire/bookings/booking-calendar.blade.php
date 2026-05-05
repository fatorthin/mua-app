<div x-data="{
    open: false,
    selectedDate: '',
    items: [],
    openDay(date, items) {
        this.selectedDate = date;
        this.items = items;
        this.open = true;
    }
}">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-lg font-semibold text-gray-800 capitalize">{{ $monthLabel }}</h3>
            <div class="flex items-center gap-2">
                <button wire:click="previousMonth" class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                    <span class="hidden sm:inline">Sebelumnya</span>
                    <span class="sm:hidden">‹</span>
                </button>
                <button wire:click="nextMonth" class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <span class="sm:hidden">›</span>
                </button>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <a href="{{ route('bookings.create') }}" wire:navigate class="flex-1 sm:flex-none bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700 text-center">
                + Tambah Booking
            </a>
            <a href="{{ route('bookings.index') }}" wire:navigate class="hidden sm:inline-flex border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                Lihat Daftar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-100">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                <div class="px-2 sm:px-3 py-2 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide text-center sm:text-left">{{ $day }}</div>
            @endforeach
        </div>

        @foreach ($calendarGrid as $week)
            <div class="grid grid-cols-7 border-b border-gray-100 last:border-b-0">
                @foreach ($week as $date)
                    @php
                        $isToday = $date === now()->toDateString();
                        $dayBookings = $date ? $bookingsByDate[$date] ?? collect() : collect();
                        $summaryItems = $dayBookings
                            ->map(
                                fn($booking) => [
                                    'id' => $booking->id,
                                    'client' => $booking->client->name,
                                    'time' => $booking->booking_date->format('H:i'),
                                    'service' => $booking->service->name,
                                    'status' => $booking->status_label,
                                    'detailUrl' => route('bookings.show', $booking),
                                ],
                            )
                            ->values();
                    @endphp
                    <div class="min-h-24 sm:min-h-32 p-1.5 sm:p-2 border-r border-gray-100 last:border-r-0 bg-white">
                        @if ($date)
                            <button type="button" @click="openDay('{{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}', @js($summaryItems))" class="w-full text-left">
                                <div class="flex items-center justify-between mb-1 sm:mb-2">
                                    <span class="text-[11px] sm:text-xs font-semibold {{ $isToday ? 'text-pink-600' : 'text-gray-600' }}">
                                        {{ \Carbon\Carbon::parse($date)->format('d') }}
                                    </span>
                                    @if ($dayBookings->count() > 0)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-pink-100 text-pink-700">
                                            {{ $dayBookings->count() }}
                                        </span>
                                    @endif
                                </div>

                                <div class="sm:hidden">
                                    @if ($dayBookings->count() > 0)
                                        <p class="text-[10px] text-gray-500">Tap untuk lihat</p>
                                    @else
                                        <p class="text-[10px] text-gray-300">Kosong</p>
                                    @endif
                                </div>
                            </button>

                            <div class="hidden sm:block space-y-1 mt-1">
                                @forelse ($dayBookings->take(3) as $booking)
                                    <a href="{{ route('bookings.show', $booking) }}" wire:navigate class="block text-[11px] p-1.5 rounded-md border border-gray-100 hover:border-pink-200 hover:bg-pink-50">
                                        <p class="font-medium text-gray-700 truncate">{{ $booking->client->name }}</p>
                                        <p class="text-gray-500">{{ $booking->booking_date->format('H:i') }} · {{ $booking->service->name }}</p>
                                    </a>
                                @empty
                                    <p class="text-[11px] text-gray-300">Tidak ada booking</p>
                                @endforelse

                                @if ($dayBookings->count() > 3)
                                    <p class="text-[11px] text-pink-600 font-medium">+{{ $dayBookings->count() - 3 }} lainnya</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div x-show="open" x-transition class="fixed inset-0 z-50 sm:hidden" style="display: none;">
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
        <div class="absolute inset-x-0 bottom-0 bg-white rounded-t-2xl p-4 max-h-[75vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <h4 class="text-base font-semibold text-gray-800">Summary Booking</h4>
                    <p class="text-xs text-gray-500" x-text="selectedDate"></p>
                </div>
                <button type="button" @click="open = false" class="px-2 py-1 text-sm rounded-md border border-gray-200 text-gray-500">Tutup</button>
            </div>

            <template x-if="items.length === 0">
                <p class="text-sm text-gray-400">Tidak ada booking pada tanggal ini.</p>
            </template>

            <div class="space-y-2" x-show="items.length > 0">
                <template x-for="item in items" :key="item.id">
                    <div class="rounded-lg border border-gray-100 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-800" x-text="item.client"></p>
                                <p class="text-xs text-gray-500" x-text="item.time + ' · ' + item.service"></p>
                                <p class="text-[11px] text-gray-400 mt-1" x-text="'Status: ' + item.status"></p>
                            </div>
                            <a :href="item.detailUrl" wire:navigate class="text-xs text-pink-600 font-medium hover:text-pink-700">Detail</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
