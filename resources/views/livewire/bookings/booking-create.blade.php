<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Tambah Booking Baru</h3>

        <form wire:submit="save" class="space-y-5">
            {{-- Client selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Klien</label>

                <div class="flex items-center gap-3 mb-2">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="radio" wire:model.live="newClient" value="0" class="text-pink-600"> Klien lama
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="radio" wire:model.live="newClient" value="1" class="text-pink-600"> Klien
                        baru
                    </label>
                </div>

                @if (!$newClient)
                    <select wire:model="client_id"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                        <option value="">-- Pilih Klien --</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}
                                {{ $client->phone ? '(' . $client->phone . ')' : '' }}</option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                @else
                    <div class="space-y-3 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <input wire:model="new_client_name" type="text" placeholder="Nama klien baru *"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                            @error('new_client_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <input wire:model="new_client_phone" type="text" placeholder="No. HP"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                        <input wire:model="new_client_email" type="email" placeholder="Email"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    </div>
                @endif
            </div>

            {{-- Service --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Layanan *</label>
                <select wire:model="service_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    <option value="">-- Pilih Layanan --</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} — {{ $service->formatted_price }}
                            ({{ $service->duration }} mnt)</option>
                    @endforeach
                </select>
                @error('service_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Date & Time --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                    <input wire:model="booking_date" type="date" min="{{ date('Y-m-d') }}"
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

            {{-- Location --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                <input wire:model="location" type="text" placeholder="Alamat/lokasi booking"
                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea wire:model="notes" rows="3" placeholder="Catatan tambahan..."
                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-pink-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-pink-700">
                    Simpan Booking
                </button>
                <a href="{{ route('bookings.index') }}" wire:navigate
                    class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
