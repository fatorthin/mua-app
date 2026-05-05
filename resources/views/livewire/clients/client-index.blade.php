<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex-1 max-w-sm">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari klien..."
                class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
        </div>
        <button wire:click="openCreate"
            class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700 whitespace-nowrap">
            + Tambah Klien
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Kontak</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Total Booking</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($clients as $client)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-semibold text-sm shrink-0">
                                        {{ strtoupper(substr($client->name, 0, 1)) }}
                                    </div>
                                    <p class="font-medium text-gray-800">{{ $client->name }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                <p>{{ $client->phone ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $client->email ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->bookings_count }} booking</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="openEdit({{ $client->id }})"
                                        class="text-xs text-gray-600 hover:underline">Edit</button>
                                    <button wire:click="delete({{ $client->id }})" wire:confirm="Hapus klien ini?"
                                        class="text-xs text-red-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-gray-400">Belum ada klien.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($clients as $client)
                <div class="p-4 flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($client->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 text-sm truncate">{{ $client->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $client->phone ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $client->bookings_count }} booking</p>
                    </div>
                    <div class="flex flex-col gap-1 items-end shrink-0">
                        <button wire:click="openEdit({{ $client->id }})"
                            class="text-xs text-gray-600 hover:underline">Edit</button>
                        <button wire:click="delete({{ $client->id }})" wire:confirm="Hapus klien ini?"
                            class="text-xs text-red-500 hover:underline">Hapus</button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-gray-400 text-sm">Belum ada klien.</div>
            @endforelse
        </div>

        @if ($clients->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $clients->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-5">
                    {{ $editingId ? 'Edit Klien' : 'Tambah Klien' }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                        <input wire:model="name" type="text"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                        <div
                            class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-1 focus-within:ring-pink-500 focus-within:border-pink-500 bg-white">
                            <span
                                class="px-3 flex items-center text-sm font-medium text-gray-500 bg-gray-100 border-r border-gray-300 select-none">62</span>
                            <input wire:model="phone" type="text" inputmode="numeric" placeholder="8123456789"
                                class="flex-1 min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                            class="flex-1 bg-pink-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-pink-700">
                            Simpan
                        </button>
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 bg-gray-100 text-gray-600 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-200">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
