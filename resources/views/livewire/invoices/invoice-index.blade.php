<div>
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Filter & Summary Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Manajemen Invoice & Keuangan</h2>
                <p class="text-xs text-gray-500 mt-0.5">Kelola penagihan, pantau status pembayaran, dan export rekapitulasi.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Month filter --}}
                <select wire:model.live="periodMonth" class="rounded-lg border-gray-300 text-xs focus:ring-pink-500 bg-white">
                    <option value="">Semua Bulan</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endforeach
                </select>

                {{-- Year filter --}}
                <select wire:model.live="periodYear" class="rounded-lg border-gray-300 text-xs focus:ring-pink-500 bg-white">
                    <option value="">Semua Tahun</option>
                    @foreach (range(date('Y'), date('Y') - 3) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>

                {{-- Export CSV button --}}
                <button wire:click="exportCsv"
                    class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-3 py-2 rounded-lg text-xs font-semibold hover:bg-emerald-700 active:scale-95 transition-all shadow-sm"
                    title="Download Rekap CSV">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>

        {{-- Horizontal Status Filter Chips --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 -mx-1 px-1 text-xs no-scrollbar">
            <button type="button" wire:click="$set('statusFilter', '')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === '' ? 'bg-pink-600 text-white shadow-sm font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua Status
            </button>
            <button type="button" wire:click="$set('statusFilter', 'unpaid')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === 'unpaid' ? 'bg-amber-600 text-white shadow-sm font-semibold' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                Belum Lunas (Unpaid)
            </button>
            <button type="button" wire:click="$set('statusFilter', 'paid')"
                class="px-3 py-1.5 rounded-full font-medium whitespace-nowrap transition-all duration-150 {{ $statusFilter === 'paid' ? 'bg-green-600 text-white shadow-sm font-semibold' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                Sudah Lunas (Paid)
            </button>
        </div>

        {{-- Summary KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-gray-100 text-sm">
            <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                <span class="text-xs text-gray-500 font-medium">Total Invoice Terbit</span>
                <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $stats['total_invoices'] }} <span class="text-xs font-normal text-gray-400">invoice</span></p>
            </div>
            <div class="bg-green-50/70 rounded-lg p-3 border border-green-100">
                <span class="text-xs text-green-700 font-medium">Total Pembayaran Lunas</span>
                <p class="text-lg font-bold text-green-800 mt-0.5">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-orange-50/70 rounded-lg p-3 border border-orange-100">
                <span class="text-xs text-orange-700 font-medium">Sisa Piutang / Belum Lunas</span>
                <p class="text-lg font-bold text-orange-800 mt-0.5">Rp {{ number_format($stats['total_unpaid'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm min-w-max">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No. Invoice</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Klien</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Layanan</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Total Tagihan</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Jatuh Tempo</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tgl Bayar</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700 font-semibold">{{ $invoice->invoice_number }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $invoice->booking->client->name }}
                                <p class="text-xs text-gray-400">{{ $invoice->booking->client->phone ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $invoice->booking->service->name }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $invoice->formatted_total }}
                                @if ($invoice->booking && $invoice->booking->is_dp_paid)
                                    <span class="block text-[11px] text-green-600 font-normal">DP: Rp {{ number_format($invoice->booking->dp_amount, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $invoice->paid_at?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $invoice->status === 'unpaid' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $invoice->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end items-center gap-2.5">
                                    <button type="button"
                                        @click="$dispatch('open-invoice-preview', { id: {{ $invoice->id }}, number: '{{ $invoice->invoice_number }}', previewUrl: '{{ route('invoices.preview', $invoice, false) }}', downloadUrl: '{{ route('invoices.download', $invoice, false) }}' })"
                                        class="text-xs font-medium text-pink-600 hover:text-pink-700 hover:underline">
                                        Lihat
                                    </button>
                                    <a href="{{ route('invoices.download', $invoice, false) }}" download target="_blank"
                                        class="text-xs font-medium text-gray-500 hover:text-gray-700" title="Unduh PDF">
                                        PDF
                                    </a>
                                    <button wire:click="resendInvoice({{ $invoice->id }})"
                                        wire:confirm="Kirim ulang invoice ini ke WhatsApp klien?"
                                        class="text-xs text-blue-600 hover:underline">Kirim WA</button>
                                    @if ($invoice->status === 'unpaid')
                                        <button wire:click="openPaymentModal({{ $invoice->id }})"
                                            class="text-xs font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded border border-emerald-200 transition-colors">
                                            Pelunasan
                                        </button>
                                    @else
                                        <button wire:click="setUnpaid({{ $invoice->id }})"
                                            wire:confirm="Ubah status invoice ini menjadi belum dibayar?"
                                            class="text-xs text-gray-500 hover:text-orange-600 hover:underline">Batalkan</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">Tidak ada data invoice.</td>
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
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-medium shrink-0
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
                            <p class="text-xs text-gray-400">Total Tagihan</p>
                            <p class="text-gray-700 font-semibold">{{ $invoice->formatted_total }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-gray-50">
                        <button type="button"
                            @click="$dispatch('open-invoice-preview', { id: {{ $invoice->id }}, number: '{{ $invoice->invoice_number }}', previewUrl: '{{ route('invoices.preview', $invoice, false) }}', downloadUrl: '{{ route('invoices.download', $invoice, false) }}' })"
                            class="inline-flex items-center gap-1 text-xs font-medium text-pink-700 bg-pink-50 hover:bg-pink-100 px-2.5 py-1 rounded-lg border border-pink-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat
                        </button>
                        <a href="{{ route('invoices.download', $invoice, false) }}" download target="_blank"
                            class="inline-flex items-center gap-1 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 px-2.5 py-1 rounded-lg border border-gray-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            PDF
                        </a>
                        <button wire:click="resendInvoice({{ $invoice->id }})"
                            wire:confirm="Kirim ulang invoice ini ke WhatsApp klien?"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 px-2 py-1">Kirim WA</button>
                        @if ($invoice->status === 'unpaid')
                            <button wire:click="openPaymentModal({{ $invoice->id }})"
                                class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                                Pelunasan
                            </button>
                        @else
                            <button wire:click="setUnpaid({{ $invoice->id }})"
                                wire:confirm="Ubah status invoice ini menjadi belum dibayar?"
                                class="text-xs font-medium text-orange-600 hover:text-orange-700 px-2 py-1">Batalkan</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">Tidak ada data invoice.</div>
            @endforelse
        </div>

        @if ($invoices->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
        @endif
    </div>

    {{-- Payment Confirmation Modal --}}
    @if ($showPaymentModal && $payingInvoice)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closePaymentModal" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-lg font-bold text-gray-800" id="modal-title">
                            Konfirmasi Pelunasan Invoice
                        </h3>
                        <button type="button" wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl space-y-1 text-sm border border-gray-200">
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-xs">No. Invoice:</span>
                            <span class="font-mono font-semibold text-gray-800">{{ $payingInvoice->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-xs">Klien:</span>
                            <span class="font-medium text-gray-800">{{ $payingInvoice->booking->client->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-xs">Total yang Dilunasi:</span>
                            <span class="font-bold text-pink-600 text-base">Rp {{ number_format($payingInvoice->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Metode Pembayaran *</label>
                            <select wire:model="paymentMethod" class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                                <option value="Transfer Bank (BCA)">Transfer Bank (BCA)</option>
                                <option value="Transfer Bank (Mandiri)">Transfer Bank (Mandiri)</option>
                                <option value="Transfer Bank (BRI)">Transfer Bank (BRI)</option>
                                <option value="Transfer Bank (BNI)">Transfer Bank (BNI)</option>
                                <option value="QRIS">QRIS / E-Wallet</option>
                                <option value="Tunai / Cash">Tunai / Cash</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" wire:model="paymentNotes" placeholder="Contoh: Transfer a.n Sarah, bukti transfer terlampir"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-pink-500">
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center gap-2 cursor-pointer select-none p-3 bg-pink-50 rounded-xl border border-pink-100">
                                <input type="checkbox" wire:model="sendReceiptWa" class="rounded border-gray-300 text-pink-600 focus:ring-pink-500 w-4 h-4">
                                <span class="text-xs font-semibold text-gray-800">Kirim pesan kuitansi pelunasan otomatis ke WhatsApp klien</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="closePaymentModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="confirmPayment"
                            class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Konfirmasi Lunas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
