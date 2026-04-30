<div>
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-xl font-bold text-gray-800">Layanan Saya</h2>
        <button wire:click="openCreate"
            class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700">
            + Tambah Layanan
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($services as $service)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $service->name }}</h4>
                        @if ($service->description)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $service->description }}</p>
                        @endif
                    </div>
                    <span
                        class="text-xs px-2 py-0.5 rounded-full {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="text-lg font-bold text-pink-600 mb-1">{{ $service->formatted_price }}</p>
                <p class="text-xs text-gray-400 mb-4">Durasi: {{ $service->duration }} menit</p>
                <div class="flex gap-2">
                    <button wire:click="openEdit({{ $service->id }})"
                        class="text-xs text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 flex-1">Edit</button>
                    <button wire:click="delete({{ $service->id }})" wire:confirm="Hapus layanan ini?"
                        class="text-xs text-red-500 border border-red-100 px-3 py-1.5 rounded-lg hover:bg-red-50 flex-1">Hapus</button>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 text-gray-400">
                Belum ada layanan. Tambahkan layanan pertama Anda!
            </div>
        @endforelse
    </div>

    {{ $services->links() }}

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="$wire.showModal = false">
                <h3 class="text-lg font-semibold text-gray-800 mb-5">
                    {{ $editingId ? 'Edit Layanan' : 'Tambah Layanan' }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan *</label>
                        <input wire:model="name" type="text" placeholder="cth: Bridal Makeup"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
                            <input wire:model="price" type="number" min="0" placeholder="500000"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                            @error('price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (mnt) *</label>
                            <input wire:model="duration" type="number" min="15" placeholder="60"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                            @error('duration')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input wire:model="is_active" type="checkbox" id="is_active" class="text-pink-600 rounded">
                        <label for="is_active" class="text-sm text-gray-600">Layanan aktif</label>
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
