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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                            <div
                                class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-1 focus-within:ring-pink-500 focus-within:border-pink-500 bg-white">
                                <span
                                    class="px-3 flex items-center text-sm font-medium text-gray-500 bg-gray-100 border-r border-gray-300 select-none">62</span>
                                <input wire:model="new_client_phone" type="text" inputmode="numeric"
                                    placeholder="8123456789"
                                    class="flex-1 min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none">
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Services --}}
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
                                onInput(e) {
                                    const raw = e.target.value.replace(/[^0-9]/g, '');
                                    this.formatted = raw ? parseInt(raw).toLocaleString('id-ID') : '';
                                    $wire.set('selectedServices.{{ $i }}.price', raw !== '' ? parseInt(raw) : '');
                                }
                            }"
                                x-effect="
                                    const p = $wire.selectedServices[{{ $i }}]?.price;
                                    formatted = (p !== '' && p !== undefined && p !== null && p !== 0 && p !== '0')
                                        ? Number(p).toLocaleString('id-ID')
                                        : '';
                                    const q = $wire.selectedServices[{{ $i }}]?.quantity;
                                    if (q !== undefined) qty = parseInt(q) || 1;
                                ">
                                {{-- Quantity with +/- buttons --}}
                                <div class="col-span-1">
                                    <label
                                        class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Jumlah</label>
                                    <div
                                        class="flex items-center rounded-lg border border-gray-300 overflow-hidden bg-white h-[38px] shadow-sm">
                                        <button type="button" x-on:click="dec()"
                                            class="w-10 h-full flex items-center justify-center text-gray-500 hover:bg-pink-50 hover:text-pink-600 border-r border-gray-300 text-lg transition-colors select-none">−</button>
                                        <span x-text="qty"
                                            class="flex-1 text-center text-sm font-semibold text-gray-700 select-none"></span>
                                        <button type="button" x-on:click="inc()"
                                            class="w-10 h-full flex items-center justify-center text-gray-500 hover:bg-pink-50 hover:text-pink-600 border-l border-gray-300 text-lg transition-colors select-none">+</button>
                                    </div>
                                    @error("selectedServices.{$i}.quantity")
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Price with Rupiah formatting --}}
                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Harga
                                        Satuan</label>
                                    <div
                                        class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-pink-100 focus-within:border-pink-500 bg-white h-[38px] shadow-sm transition-shadow">
                                        <span
                                            class="px-3 flex items-center text-sm font-medium text-gray-500 bg-gray-50 border-r border-gray-300 select-none">Rp</span>
                                        <input type="text" inputmode="numeric" x-bind:value="formatted"
                                            x-on:input="onInput" placeholder="0"
                                            class="w-full min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none font-medium text-gray-700">
                                    </div>
                                    @error("selectedServices.{$i}.price")
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('selectedServices')
                    <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror

                <button type="button" wire:click="addService"
                    class="mt-3 flex items-center gap-2 text-sm font-medium text-pink-600 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-4 py-2.5 rounded-lg transition-colors w-max">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Tambah Layanan Lainnya
                </button>

                @php
                    $summaryItems = collect($selectedServices)
                        ->map(function ($row) use ($services) {
                            $service = $services->firstWhere('id', (int) ($row['service_id'] ?? 0));
                            if (!$service) {
                                return null;
                            }

                            $qty = max(1, (int) ($row['quantity'] ?? 1));
                            $price = (float) ($row['price'] ?? 0);

                            return [
                                'name' => $service->name,
                                'qty' => $qty,
                                'subtotal' => $qty * $price,
                            ];
                        })
                        ->filter()
                        ->values();

                    $summaryTotal = $summaryItems->sum('subtotal');
                @endphp

                <div
                    class="mt-6 rounded-xl border border-pink-100 bg-gradient-to-br from-pink-50 to-white p-5 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Ringkasan Biaya
                    </h4>

                    @if ($summaryItems->isEmpty())
                        <p class="text-sm text-gray-500 italic text-center py-2">Pilih layanan untuk melihat ringkasan
                            biaya.</p>
                    @else
                        <div class="space-y-3 mb-4">
                            @foreach ($summaryItems as $summary)
                                <div class="flex items-start justify-between text-sm text-gray-600">
                                    <div class="flex-1 pr-4 leading-tight">
                                        <span class="font-medium text-gray-700">{{ $summary['name'] }}</span>
                                        <span
                                            class="text-gray-400 text-xs ml-1 font-semibold">x{{ $summary['qty'] }}</span>
                                    </div>
                                    <span class="font-medium text-gray-800 whitespace-nowrap">Rp
                                        {{ number_format($summary['subtotal'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-pink-200 border-dashed flex items-center justify-between">
                            <span class="text-base font-bold text-gray-800">Total Pembayaran</span>
                            <span class="text-xl font-black text-pink-600">Rp
                                {{ number_format($summaryTotal, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4 space-y-3">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model.live="is_dp_paid"
                            class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                        Klien sudah membayar DP
                    </label>

                    @if ($is_dp_paid)
                        <div x-data="{ formatted: '' }"
                            x-effect="
                                const p = $wire.dp_amount;
                                formatted = (p !== '' && p !== undefined && p !== null && p !== 0 && p !== '0')
                                    ? Number(p).toLocaleString('id-ID')
                                    : '';
                            ">
                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Nominal
                                DP</label>
                            <div
                                class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-pink-100 focus-within:border-pink-500 bg-white h-[38px]">
                                <span
                                    class="px-3 flex items-center text-sm font-medium text-gray-500 bg-gray-50 border-r border-gray-300 select-none">Rp</span>
                                <input type="text" inputmode="numeric" x-bind:value="formatted"
                                    x-on:input="
                                        const raw = $event.target.value.replace(/[^0-9]/g, '');
                                        formatted = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
                                        $wire.set('dp_amount', raw !== '' ? parseInt(raw, 10) : '', false);
                                    "
                                    placeholder="0"
                                    class="w-full min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none font-medium text-gray-700">
                            </div>
                            @error('dp_amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi (opsional)</label>
                <div
                    class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-1 focus-within:ring-pink-500 focus-within:border-pink-500 bg-white">
                    <span
                        class="px-2 flex items-center bg-gray-100 border-r border-gray-300 text-gray-500 select-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input wire:model="location" type="text" placeholder="Alamat / link maps (opsional)"
                        class="flex-1 min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none">
                </div>
                @error('location')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
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
