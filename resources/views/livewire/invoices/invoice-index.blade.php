<div>
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h2 class="text-xl font-bold text-gray-800">Invoice</h2>
        <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 text-sm focus:ring-pink-500 w-48">
            <option value="">Semua Status</option>
            <option value="unpaid">Belum Dibayar</option>
            <option value="paid">Sudah Dibayar</option>
        </select>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm min-w-max">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No. Invoice</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Klien</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Layanan</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Total</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Jatuh Tempo</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tgl Bayar</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $invoice->invoice_number }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $invoice->booking->client->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $invoice->booking->service->name }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-700">{{ $invoice->formatted_total }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $invoice->paid_at?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $invoice->status === 'unpaid' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $invoice->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end items-center gap-3">
                                    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="text-xs font-medium text-pink-600 hover:text-pink-700">Preview (PDF)
                                    </a>
                                    <button wire:click="resendInvoice({{ $invoice->id }})" wire:confirm="Kirim ulang invoice ini ke WhatsApp klien?" class="text-xs text-blue-600 hover:underline">Kirim Ulang WA</button>
                                    @if ($invoice->status === 'unpaid')
                                        <button wire:click="updateStatus({{ $invoice->id }}, 'paid')" wire:confirm="Tandai invoice ini sebagai lunas?" class="text-xs text-green-600 hover:underline">Tandai Lunas</button>
                                    @else
                                        <button wire:click="updateStatus({{ $invoice->id }}, 'unpaid')" wire:confirm="Ubah status invoice ini kembali menjadi belum dibayar?" class="text-xs text-orange-600 hover:underline">Set Belum Lunas</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada invoice.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($invoices as $invoice)
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-800">{{ $invoice->booking->client->name }}</p>
                            <p class="font-mono text-xs text-gray-500">{{ $invoice->invoice_number }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium shrink-0
                            {{ $invoice->status === 'unpaid' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                            {{ $invoice->status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="col-span-2">
                            <p class="text-xs text-gray-400">Layanan</p>
                            <p class="text-gray-700">{{ $invoice->booking->service->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Jatuh Tempo</p>
                            <p class="text-gray-700">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Total</p>
                            <p class="text-gray-700 font-medium">{{ $invoice->formatted_total }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3 pt-2 border-t border-gray-50">
                        <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="text-sm font-medium text-pink-600 hover:text-pink-700">Preview (PDF)</a>
                        <button wire:click="resendInvoice({{ $invoice->id }})" wire:confirm="Kirim ulang invoice ini ke WhatsApp klien?" class="text-sm font-medium text-blue-600 hover:text-blue-700">Kirim Ulang WA</button>
                        @if ($invoice->status === 'unpaid')
                            <button wire:click="updateStatus({{ $invoice->id }}, 'paid')" class="text-sm font-medium text-green-600 hover:text-green-700">Lunas</button>
                        @else
                            <button wire:click="updateStatus({{ $invoice->id }}, 'unpaid')" class="text-sm font-medium text-orange-600 hover:text-orange-700">Batalkan</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">Belum ada invoice.</div>
            @endforelse
        </div>

        @if ($invoices->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
