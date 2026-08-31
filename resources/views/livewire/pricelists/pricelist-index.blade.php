<div>
    {{-- Header & Action --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>📋</span>
                <span>Dokumen Pricelist</span>
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Kelola brosur & katalog paket harga MUA, unduh PDF/JPG, dan bagikan langsung ke WhatsApp klien.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pricelists.create') }}" wire:navigate
                class="inline-flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Pricelist Baru</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="mb-6 rounded-xl bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show"
            class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Search Bar --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Cari judul dokumen pricelist..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition shadow-sm">
        </div>
    </div>

    {{-- Pricelist Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($pricelists as $pricelist)
            @php
                $themeBadge = match($pricelist->theme_template) {
                    'luxury_gold' => ['label' => 'Luxury Noir & Gold', 'bg' => 'bg-amber-100 text-amber-800 border-amber-200'],
                    'clean_nude' => ['label' => 'Clean Nude & Earthy', 'bg' => 'bg-orange-100 text-orange-800 border-orange-200'],
                    'sage_botanical' => ['label' => 'Sage Botanical', 'bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                    default => ['label' => 'Rose Blush', 'bg' => 'bg-pink-100 text-pink-800 border-pink-200'],
                };

                // Generate formatted draft message for direct WA clipboard
                $waTextSummary = "✨ *PRICELIST " . strtoupper($pricelist->title) . "* ✨\n" .
                                 ($pricelist->user->studio_name ?: $pricelist->user->name) . "\n\n";

                foreach($pricelist->sections as $sec) {
                    $waTextSummary .= "📌 *" . strtoupper($sec->name) . "*\n";
                    foreach($sec->items as $it) {
                        $waTextSummary .= "• *" . $it->name . "* : " . $it->formatted_price . ($it->duration_text ? " (" . $it->duration_text . ")" : "") . "\n";
                        if (!empty($it->features) && count($it->features) > 0) {
                            $waTextSummary .= "  Benefit: " . implode(', ', array_slice($it->features, 0, 3)) . "\n";
                        }
                    }
                    $waTextSummary .= "\n";
                }

                $waTextSummary .= "🔗 *Lihat Brosur Interaktif:* " . $pricelist->public_url . "\n\n" .
                                 "Info & Booking: WA " . ($pricelist->user->phone ?: '-');
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition duration-200 overflow-hidden flex flex-col justify-between"
                x-data="{ 
                    copiedLink: false,
                    copiedText: false,
                    async copyLink(url) {
                        const success = await window.copyToClipboard(url);
                        if (success !== false) {
                            this.copiedLink = true;
                            setTimeout(() => this.copiedLink = false, 2500);
                        }
                    },
                    async copyDraft(text) {
                        const success = await window.copyToClipboard(text);
                        if (success !== false) {
                            this.copiedText = true;
                            setTimeout(() => this.copiedText = false, 2500);
                        }
                    }
                }">
                <div>
                    {{-- Card Top Banner / Accent --}}
                    <div class="h-3 w-full" style="background-color: {{ $pricelist->primary_color ?: '#ec4899' }};"></div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 text-base leading-snug line-clamp-1">
                                    {{ $pricelist->title }}
                                </h3>
                                @if($pricelist->description)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $pricelist->description }}</p>
                                @endif
                            </div>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full border shrink-0 {{ $themeBadge['bg'] }}">
                                {{ $themeBadge['label'] }}
                            </span>
                        </div>

                        {{-- Stats & Info --}}
                        <div class="grid grid-cols-2 gap-2 my-4 py-3 border-y border-gray-100 bg-gray-50/70 rounded-xl px-3 text-center">
                            <div>
                                <span class="block text-xs text-gray-400">Kategori</span>
                                <span class="font-bold text-gray-800 text-sm">{{ $pricelist->sections_count }} Kategori</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-400">Total Paket</span>
                                <span class="font-bold text-gray-800 text-sm">{{ $pricelist->items_count }} Paket</span>
                            </div>
                        </div>

                        {{-- Quick Sharing & Export Badges --}}
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            {{-- Public link copy --}}
                            <button type="button" @click="copyLink('{{ $pricelist->public_url }}')"
                                class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-2.5 py-1 rounded-lg text-xs font-medium transition">
                                <span x-show="!copiedLink">🔗 Salin Link</span>
                                <span x-show="copiedLink" class="text-green-600 font-bold" x-cloak>✓ Link Disalin!</span>
                            </button>

                            {{-- WA format copy --}}
                            <button type="button" @click="copyDraft(@js($waTextSummary))"
                                class="inline-flex items-center gap-1 bg-pink-50 hover:bg-pink-100 text-pink-700 px-2.5 py-1 rounded-lg text-xs font-medium transition">
                                <span x-show="!copiedText">💬 Teks WA</span>
                                <span x-show="copiedText" class="text-green-600 font-bold" x-cloak>✓ Teks Disalin!</span>
                            </button>

                            {{-- PDF Download --}}
                            <a href="{{ route('pricelists.pdf', $pricelist) }}" target="_blank"
                                class="inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 text-rose-700 px-2 py-1 rounded-lg text-xs font-medium transition" title="Unduh PDF Dokumen">
                                <span>📄 PDF</span>
                            </a>

                            {{-- JPG Download --}}
                            <a href="{{ route('pricelists.jpg', $pricelist) }}" target="_blank"
                                class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 px-2 py-1 rounded-lg text-xs font-medium transition" title="Unduh Gambar JPG">
                                <span>🖼️ JPG</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2">
                    {{-- Open Builder --}}
                    <a href="{{ route('pricelists.edit', $pricelist) }}" wire:navigate
                        class="flex-1 inline-flex items-center justify-center gap-1 bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:text-pink-600 px-3 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Builder</span>
                    </a>

                    {{-- Send via WA gateway --}}
                    <button wire:click="openSendWaModal({{ $pricelist->id }})" title="Kirim PDF via WhatsApp Gateway"
                        class="p-2 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 rounded-xl text-xs transition shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                    </button>

                    {{-- Duplicate --}}
                    <button wire:click="duplicate({{ $pricelist->id }})" title="Duplikat Dokumen"
                        class="p-2 bg-white border border-gray-200 text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-xl text-xs transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>

                    {{-- Delete --}}
                    <button wire:click="delete({{ $pricelist->id }})" wire:confirm="Yakin ingin menghapus dokumen pricelist ini?" title="Hapus Dokumen"
                        class="p-2 bg-white border border-red-100 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl text-xs transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 px-4 text-center bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="w-16 h-16 bg-pink-50 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    💄
                </div>
                <h3 class="text-base font-semibold text-gray-800">Belum Ada Dokumen Pricelist</h3>
                <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto mt-1 mb-5">
                    Buat brosur paket harga layanan Anda sekarang agar calon klien mendapatkan penawaran yang terstruktur dan memikat.
                </p>
                <a href="{{ route('pricelists.create') }}" wire:navigate
                    class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Buat Pricelist Pertama</span>
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $pricelists->links() }}
    </div>

    {{-- Send WhatsApp Modal --}}
    @if ($showWaModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="$wire.showWaModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 overflow-hidden border border-gray-100"
                 @click.outside="$wire.showWaModal = false">
                
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-sm">
                            💬
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Kirim Pricelist via WhatsApp</h3>
                            <p class="text-xs text-gray-500">{{ $waSelectedPricelistTitle }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showWaModal', false)" class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="sendWa" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nomor WhatsApp Penerima *</label>
                        <input wire:model="waRecipientPhone" type="text" placeholder="cth: 08123456789 atau 628123456789"
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-green-500 focus:border-green-500">
                        @error('waRecipientPhone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Pesan Pengantar (Caption)</label>
                        <textarea wire:model="waCustomMessage" rows="4"
                            class="w-full rounded-xl border-gray-200 text-xs text-gray-700 focus:ring-green-500 focus:border-green-500"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Dokumen PDF Pricelist resmi akan otomatis dilampirkan bersama pesan ini.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="$set('showWaModal', false)"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-100 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition disabled:opacity-50">
                            <svg wire:loading wire:target="sendWa" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Kirim Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
