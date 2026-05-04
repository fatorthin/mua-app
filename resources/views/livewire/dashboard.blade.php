<div>
    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">Booking Hari Ini Guys hohoho</p>
            <p class="text-2xl font-bold text-pink-600">{{ $stats['today_bookings'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">Total Bookingzzzz</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['total_bookings'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">Total Klien</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_clients'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">Layanan Aktif</p>
            <p class="text-2xl font-bold text-teal-600">{{ $stats['total_services'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">Pendapatan Bulan Ini</p>
            <p class="text-xl font-bold text-green-600">Rp {{ number_format($stats['revenue_month'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">Invoice Belum Dibayar</p>
            <p class="text-2xl font-bold text-orange-500">{{ $stats['pending_invoices'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Upcoming Bookings --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Booking Mendatang</h3>
                <a href="{{ route('bookings.index') }}" wire:navigate
                    class="text-xs text-pink-600 hover:underline">Lihat semua</a>
            </div>
            @forelse($upcomingBookings as $booking)
                <div class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0">
                    <div
                        class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($booking->client->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-gray-800 truncate">{{ $booking->client->name }}</p>
                        <p class="text-xs text-gray-500">{{ $booking->service->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $booking->booking_date->format('d M Y, H:i') }}</p>
                    </div>
                    <span
                        class="px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $booking->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $booking->status_label }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Tidak ada booking mendatang</p>
            @endforelse
        </div>

        {{-- Recent Bookings --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Booking Terbaru</h3>
                <a href="{{ route('bookings.create') }}" wire:navigate
                    class="text-xs bg-pink-600 text-white px-3 py-1.5 rounded-lg hover:bg-pink-700">+ Tambah</a>
            </div>
            @forelse($recentBookings as $booking)
                <div class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-gray-800 truncate">{{ $booking->client->name }}</p>
                        <p class="text-xs text-gray-500">{{ $booking->service->name }} &middot;
                            {{ $booking->booking_date->format('d M Y') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">{{ $booking->formatted_price }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada booking</p>
            @endforelse
        </div>
    </div>
</div>
