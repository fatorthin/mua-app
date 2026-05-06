<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h2 class="text-xl font-bold text-gray-800">Kelola Pengguna</h2>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..."
            class="rounded-lg border-gray-300 text-sm focus:ring-pink-500 w-full sm:w-64">
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 text-sm px-4 py-2 border border-green-100">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 text-sm px-4 py-2 border border-red-100">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-3 md:hidden">
        @forelse($users as $user)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-800 leading-tight">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500 break-all">{{ $user->email }}</p>
                    </div>
                    <div class="text-right space-y-1">
                        <span
                            class="inline-flex px-2 py-0.5 rounded text-xs {{ $user->role === 'admin' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-600' }}">{{ $user->role }}</span>
                        <span
                            class="inline-flex px-2 py-0.5 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-500">
                    <div>Booking: <span class="font-semibold text-gray-700">{{ $user->bookings_count }}</span></div>
                    <div>Klien: <span class="font-semibold text-gray-700">{{ $user->clients_count }}</span></div>
                    <div class="col-span-2">Bergabung: <span
                            class="font-semibold text-gray-700">{{ $user->created_at->format('d M Y') }}</span></div>
                </div>

                <div class="mt-3">
                    <button wire:click="toggleActive({{ $user->id }})"
                        wire:confirm="{{ $user->is_active ? 'Nonaktifkan user ini?' : 'Aktifkan kembali user ini?' }}"
                        class="w-full px-3 py-2 rounded-lg text-sm font-medium {{ $user->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                        {{ $user->is_active ? 'Nonaktifkan User' : 'Aktifkan User' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-12 text-center text-gray-400">
                Tidak ada pengguna.
            </div>
        @endforelse
    </div>

    <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[860px]">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Role</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Booking</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Klien</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Bergabung</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-0.5 rounded text-xs {{ $user->role === 'admin' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-0.5 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->bookings_count }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->clients_count }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <button wire:click="toggleActive({{ $user->id }})"
                                    wire:confirm="{{ $user->is_active ? 'Nonaktifkan user ini?' : 'Aktifkan kembali user ini?' }}"
                                    class="text-xs font-medium px-3 py-1.5 rounded-lg {{ $user->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">Tidak ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>

    @if ($users->hasPages())
        <div class="md:hidden mt-3">{{ $users->links() }}</div>
    @endif
</div>
