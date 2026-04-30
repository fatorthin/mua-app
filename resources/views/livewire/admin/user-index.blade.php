<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h2 class="text-xl font-bold text-gray-800">Kelola Pengguna</h2>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..."
            class="rounded-lg border-gray-300 text-sm focus:ring-pink-500 w-64">
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Role</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Booking</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Klien</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Bergabung</th>
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
                        <td class="px-4 py-3 text-gray-600">{{ $user->bookings_count }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->clients_count }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">Tidak ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>
</div>
