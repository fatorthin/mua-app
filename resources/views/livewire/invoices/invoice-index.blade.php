<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h2 class="text-xl font-bold text-gray-800">Invoice</h2>
        <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 text-sm focus:ring-pink-500 w-48">
            <option value="">Semua Status</option>
            <option value="unpaid">Belum Dibayar</option>
            <option value="paid">Sudah Dibayar</option>
        </select>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">No. Invoice</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Klien</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Layanan</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Total</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Jatuh Tempo</th>
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
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $invoice->status === 'unpaid' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @if ($invoice->status === 'unpaid')
                                    <button wire:click="markPaid({{ $invoice->id }})"
                                        wire:confirm="Tandai invoice ini sebagai lunas?"
                                        class="text-xs text-green-600 hover:underline">Tandai Lunas</button>
                                @endif
                                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                                    class="text-xs text-pink-600 hover:underline">Download PDF</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada invoice.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($invoices->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
