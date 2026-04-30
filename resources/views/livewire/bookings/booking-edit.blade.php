<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Edit Booking</h3>

        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Klien *</label>
                <select wire:model="client_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    <option value="">-- Pilih Klien --</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Layanan *</label>
                <select wire:model="service_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    <option value="">-- Pilih Layanan --</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} — {{ $service->formatted_price }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                    <input wire:model="booking_date" type="date"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    @error('booking_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam *</label>
                    <input wire:model="booking_time" type="time"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    @error('booking_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    <option value="pending">Menunggu</option>
                    <option value="confirmed">Dikonfirmasi</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                <input wire:model="location" type="text"
                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea wire:model="notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-pink-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-pink-700">
                    Perbarui Booking
                </button>
                <a href="{{ route('bookings.index') }}" wire:navigate
                    class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
