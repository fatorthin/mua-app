<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Edit Booking</h3>
            <span
                class="px-3 py-1 rounded-full text-xs font-medium
                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $booking->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : '' }}
                {{ $booking->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                {{ $booking->status_label }}
            </span>
        </div>

        <form wire:submit="save" class="space-y-5">
            {{-- Client selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Klien *</label>
                <select wire:model="client_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                    <option value="">-- Pilih Klien --</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}
                            {{ $client->phone ? '(' . $client->phone . ')' : '' }}</option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Multi Services --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Layanan *</label>

                <div class="space-y-3">
                    @foreach ($selectedServices as $i => $item)
                        <div class="p-4 bg-gray-50 rounded-xl space-y-3 border border-gray-200 relative group">
                            {{-- Row 1: Service dropdown + remove button --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <label
                                        class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Pilih
                                        Layanan</label>
                                    <select wire:model.live="selectedServices.{{ $i }}.service_id"
                                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500 bg-white shadow-sm">
                                        <option value="">-- Pilih Layanan --</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}
                                                ({{ $service->duration }} menit)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("selectedServices.{$i}.service_id")
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="button" wire:click="removeService({{ $i }})"
                                    @if (count($selectedServices) <= 1) disabled @endif
                                    class="mt-5 text-gray-400 hover:text-red-500 disabled:opacity-30 shrink-0 p-2 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100"
                                    title="Hapus Layanan">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Row 2: Qty + Price --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" x-data="{
                                qty: {{ (int) ($item['quantity'] ?? 1) }},
                                formatted: '',
                                dec() {
                                    if (this.qty > 1) {
                                        this.qty--;
                                        $wire.set('selectedServices.{{ $i }}.quantity', this.qty);
                                    }
                                },
                                inc() {
                                    this.qty++;
                                    $wire.set('selectedServices.{{ $i }}.quantity', this.qty);
                                },
                                init() {
                                    this.updateFormatted($wire.get('selectedServices.{{ $i }}.price'));
                                    $watch(() => $wire.get('selectedServices.{{ $i }}.price'), val => this.updateFormatted(val));
                                    $watch(() => $wire.get('selectedServices.{{ $i }}.quantity'), val => this.qty = parseInt(val) || 1);
                                },
                                updateFormatted(val) {
                                    if (!val) { this.formatted = ''; return; }
                                    let num = val.toString().replace(/[^0-9]/g, '');
                                    this.formatted = num ? parseInt(num, 10).toLocaleString('id-ID') : '';
                                },
                                handleInput(e) {
                                    let raw = e.target.value.replace(/[^0-9]/g, '');
                                    this.formatted = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
                                    $wire.set('selectedServices.{{ $i }}.price', raw);
                                }
                            }">
                                {{-- Qty with stepper --}}
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Jumlah</label>
                                    <div class="flex items-center rounded-lg border border-gray-300 bg-white overflow-hidden shadow-sm">
                                        <button type="button" @click="dec()"
                                            class="px-3 py-2 text-gray-600 hover:bg-gray-100 active:bg-gray-200 border-r border-gray-200 select-none text-base font-bold leading-none">
                                            −
                                        </button>
                                        <input type="number" min="1" x-model.number="qty"
                                            @input="$wire.set('selectedServices.{{ $i }}.quantity', qty)"
                                            class="w-full text-center py-2 px-1 text-sm border-0 focus:ring-0 focus:outline-none [-moz-appearance:textfield] [&::-webkit-outer-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none font-medium text-gray-700">
                                        <button type="button" @click="inc()"
                                            class="px-3 py-2 text-gray-600 hover:bg-gray-100 active:bg-gray-200 border-l border-gray-200 select-none text-base font-bold leading-none">
                                            +
                                        </button>
                                    </div>
                                    @error("selectedServices.{$i}.quantity")
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Price input --}}
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Harga Satuan (Rp)</label>
                                    <div class="relative rounded-lg shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-400 text-xs font-semibold">Rp</span>
                                        </div>
                                        <input type="text" x-model="formatted" @input="handleInput($event)"
                                            placeholder="0"
                                            class="w-full rounded-lg border-gray-300 pl-9 pr-3 py-2 text-sm focus:ring-pink-500 bg-white text-right font-medium text-gray-700">
                                    </div>
                                    @error("selectedServices.{$i}.price")
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Subtotal --}}
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Subtotal Item</label>
                                    <div class="h-10 px-3 py-2 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-end text-sm font-semibold text-gray-700">
                                        @php
                                            $itemQty = (float) ($item['quantity'] ?? 1);
                                            $itemPrice = (float) ($item['price'] ?? 0);
                                            $itemSubtotal = $itemQty * $itemPrice;
                                        @endphp
                                        Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('selectedServices')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror

                <button type="button" wire:click="addService"
                    class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-pink-600 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-3 py-2 rounded-lg border border-pink-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Tambah Layanan Lain
                </button>
            </div>

            {{-- Biaya Transport --}}
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Biaya Transport (Opsional)
                </label>
                <div x-data="{
                    formattedTransport: '',
                    init() {
                        this.updateFormatted($wire.get('transport_fee'));
                        $watch(() => $wire.get('transport_fee'), val => this.updateFormatted(val));
                    },
                    updateFormatted(val) {
                        if (!val) { this.formattedTransport = ''; return; }
                        let num = val.toString().replace(/[^0-9]/g, '');
                        this.formattedTransport = num ? parseInt(num, 10).toLocaleString('id-ID') : '';
                    },
                    handleInput(e) {
                        let raw = e.target.value.replace(/[^0-9]/g, '');
                        this.formattedTransport = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
                        $wire.set('transport_fee', raw);
                    }
                }" class="relative rounded-lg shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-xs font-semibold">Rp</span>
                    </div>
                    <input type="text" x-model="formattedTransport" @input="handleInput($event)"
                        placeholder="0 (jika ada ongkos transport)"
                        class="w-full rounded-lg border-gray-300 pl-9 pr-3 py-2 text-sm focus:ring-pink-500 bg-white font-medium text-gray-700">
                </div>
                @error('transport_fee')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Down Payment --}}
            <div class="p-4 bg-pink-50/50 rounded-xl border border-pink-100 space-y-3">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="is_dp_paid"
                        class="rounded border-gray-300 text-pink-600 focus:ring-pink-500 w-4 h-4">
                    <span class="text-sm font-semibold text-gray-800">Klien sudah bayar DP (Down Payment)</span>
                </label>

                @if ($is_dp_paid)
                    <div x-data="{
                        formattedDp: '',
                        init() {
                            this.updateFormatted($wire.get('dp_amount'));
                            $watch(() => $wire.get('dp_amount'), val => this.updateFormatted(val));
                        },
                        updateFormatted(val) {
                            if (!val) { this.formattedDp = ''; return; }
                            let num = val.toString().replace(/[^0-9]/g, '');
                            this.formattedDp = num ? parseInt(num, 10).toLocaleString('id-ID') : '';
                        },
                        handleInput(e) {
                            let raw = e.target.value.replace(/[^0-9]/g, '');
                            this.formattedDp = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
                            $wire.set('dp_amount', raw);
                        }
                    }">
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Nominal
                            DP (Rp) *</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-xs font-semibold">Rp</span>
                            </div>
                            <input type="text" x-model="formattedDp" @input="handleInput($event)"
                                placeholder="Contoh: 100.000"
                                class="w-full rounded-lg border-gray-300 pl-9 pr-3 py-2 text-sm focus:ring-pink-500 bg-white font-medium text-gray-700">
                        </div>
                        @error('dp_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            {{-- Date & Time --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

            {{-- Status & Location --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Booking</label>
                    <select wire:model="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                        <option value="pending">Menunggu</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input wire:model="location" type="text" placeholder="Alamat / Lokasi acara"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea wire:model="notes" rows="3" placeholder="Catatan khusus, request makeup, dll."
                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500"></textarea>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit"
                    class="bg-pink-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-pink-700 shadow-sm transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('bookings.index') }}" wire:navigate
                    class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
