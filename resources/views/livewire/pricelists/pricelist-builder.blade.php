<div class="space-y-6">
    {{-- Top Action Bar --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('pricelists.index') }}" wire:navigate
                class="p-2 rounded-xl text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span>✨</span>
                    <span>{{ $pricelistId ? 'Edit Dokumen Pricelist' : 'Pricelist Builder & Studio Designer' }}</span>
                </h1>
                <p class="text-xs text-gray-500">Atur tata letak, warna tema, rincian paket rias, dan preview dokumen secara langsung.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 self-end sm:self-auto">
            {{-- Preview Mode Selector --}}
            <div class="hidden md:flex items-center bg-gray-100 p-1 rounded-xl text-xs font-medium text-gray-600">
                <button type="button" wire:click="$set('previewMode', 'desktop')"
                    class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition {{ $previewMode === 'desktop' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'hover:text-gray-900' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>A4 / Desktop</span>
                </button>
                <button type="button" wire:click="$set('previewMode', 'mobile')"
                    class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition {{ $previewMode === 'mobile' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'hover:text-gray-900' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Mobile Phone</span>
                </button>
            </div>

            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition disabled:opacity-50">
                <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Simpan Pricelist</span>
            </button>
        </div>
    </div>

    {{-- Main Grid: Left (Builder Controls) & Right (Live Preview) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- SISI KIRI: Controls & Form Editor (7 Cols on LG) --}}
        <div class="lg:col-span-6 xl:col-span-6 space-y-5">
            
            {{-- Tabs Navigation --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-1.5 shadow-sm flex items-center justify-between gap-1 overflow-x-auto">
                <button type="button" wire:click="$set('activeTab', 'general')"
                    class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm font-semibold text-center whitespace-nowrap transition {{ $activeTab === 'general' ? 'bg-pink-50 text-pink-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                    🎨 Branding & Tema
                </button>
                <button type="button" wire:click="$set('activeTab', 'content')"
                    class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm font-semibold text-center whitespace-nowrap transition {{ $activeTab === 'content' ? 'bg-pink-50 text-pink-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                    📦 Kategori & Paket ({{ count($sections) }})
                </button>
                <button type="button" wire:click="$set('activeTab', 'terms')"
                    class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm font-semibold text-center whitespace-nowrap transition {{ $activeTab === 'terms' ? 'bg-pink-50 text-pink-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                    📜 S&K / T&C
                </button>
                <button type="button" wire:click="$set('activeTab', 'footer')"
                    class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm font-semibold text-center whitespace-nowrap transition {{ $activeTab === 'footer' ? 'bg-pink-50 text-pink-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                    💳 Rekening & Footer
                </button>
            </div>

            {{-- TAB 1: BRANDING & TEMA --}}
            <div x-show="$wire.activeTab === 'general'" class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm space-y-5">
                <div>
                    <h3 class="text-base font-bold text-gray-800 mb-1">Informasi Dokumen</h3>
                    <p class="text-xs text-gray-500">Judul brosur dan deskripsi pembuka untuk klien.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Judul Dokumen *</label>
                        <input wire:model.live.debounce.300ms="title" type="text" placeholder="cth: Pricelist & Wedding Package 2026"
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-pink-500 focus:border-pink-500 shadow-sm">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Catatan Pembuka / Tagline</label>
                        <textarea wire:model.live.debounce.300ms="description" rows="2" placeholder="Pilihan paket tata rias eksklusif..."
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-pink-500 focus:border-pink-500 shadow-sm"></textarea>
                    </div>

                    {{-- Theme Selection Grid --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Preset Desain & Estetika</label>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Theme 1: Rose Blush --}}
                            <label class="relative flex flex-col p-3 rounded-xl border-2 cursor-pointer transition {{ $theme_template === 'rose_blush' ? 'border-pink-500 bg-pink-50/40 ring-1 ring-pink-500' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="theme_template" value="rose_blush" class="sr-only">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-4 h-4 rounded-full bg-pink-500"></div>
                                    <span class="font-bold text-xs text-gray-900">Rose Blush</span>
                                </div>
                                <p class="text-[11px] text-gray-500">Romantis, feminin & elegan (khas MUA wedding).</p>
                            </label>

                            {{-- Theme 2: Luxury Gold --}}
                            <label class="relative flex flex-col p-3 rounded-xl border-2 cursor-pointer transition {{ $theme_template === 'luxury_gold' ? 'border-amber-500 bg-amber-50/40 ring-1 ring-amber-500' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="theme_template" value="luxury_gold" class="sr-only">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-4 h-4 rounded-full bg-amber-500"></div>
                                    <span class="font-bold text-xs text-gray-900">Luxury Noir & Gold</span>
                                </div>
                                <p class="text-[11px] text-gray-500">High-end, mewah & eksklusif.</p>
                            </label>

                            {{-- Theme 3: Clean Nude --}}
                            <label class="relative flex flex-col p-3 rounded-xl border-2 cursor-pointer transition {{ $theme_template === 'clean_nude' ? 'border-orange-400 bg-orange-50/40 ring-1 ring-orange-400' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="theme_template" value="clean_nude" class="sr-only">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-4 h-4 rounded-full bg-amber-700"></div>
                                    <span class="font-bold text-xs text-gray-900">Clean Earthy Nude</span>
                                </div>
                                <p class="text-[11px] text-gray-500">Minimalis, hangat & modern studio.</p>
                            </label>

                            {{-- Theme 4: Sage Botanical --}}
                            <label class="relative flex flex-col p-3 rounded-xl border-2 cursor-pointer transition {{ $theme_template === 'sage_botanical' ? 'border-emerald-500 bg-emerald-50/40 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="theme_template" value="sage_botanical" class="sr-only">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-4 h-4 rounded-full bg-emerald-600"></div>
                                    <span class="font-bold text-xs text-gray-900">Sage Botanical</span>
                                </div>
                                <p class="text-[11px] text-gray-500">Fresh, alami, tenang & estetik.</p>
                            </label>
                        </div>
                    </div>

                    {{-- Primary Color Picker --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Warna Aksen Utama</label>
                        <div class="flex items-center gap-3">
                            <input type="color" wire:model.live="primary_color" class="w-10 h-10 rounded-xl border border-gray-200 cursor-pointer p-0.5">
                            <input type="text" wire:model.live="primary_color" class="w-32 rounded-xl border-gray-200 text-sm font-mono focus:ring-pink-500">
                            <span class="text-xs text-gray-400">Digunakan untuk tombol, header highlight, dan icon check.</span>
                        </div>
                    </div>

                    {{-- Toggles --}}
                    <div class="pt-3 border-t border-gray-100 space-y-3">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm font-medium text-gray-800">Tampilkan Logo Studio</span>
                                <p class="text-xs text-gray-400">Gunakan logo yang diupload pada profil studio Anda.</p>
                            </div>
                            <input type="checkbox" wire:model.live="show_logo" class="rounded text-pink-600 focus:ring-pink-500 h-4 w-4">
                        </label>

                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm font-medium text-gray-800">Tampilkan Media Sosial (IG & TikTok)</span>
                                <p class="text-xs text-gray-400">Menyertakan akun sosmed studio di bagian header dokumen.</p>
                            </div>
                            <input type="checkbox" wire:model.live="show_social_media" class="rounded text-pink-600 focus:ring-pink-500 h-4 w-4">
                        </label>

                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm font-medium text-gray-800">Tampilkan Tombol Booking / WhatsApp</span>
                                <p class="text-xs text-gray-400">Memudahkan calon klien langsung menghubungi kontak studio.</p>
                            </div>
                            <input type="checkbox" wire:model.live="show_contact_button" class="rounded text-pink-600 focus:ring-pink-500 h-4 w-4">
                        </label>
                    </div>
                </div>
            </div>

            {{-- TAB 2: KATEGORI & PAKET LAYANAN --}}
            <div x-show="$wire.activeTab === 'content'" class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Kategori & Paket Layanan</h3>
                        <p class="text-xs text-gray-500">Kelompokkan layanan ke dalam section (misal: Wedding, Wisuda, Add-on).</p>
                    </div>
                    <button type="button" wire:click="addSection"
                        class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-black text-white px-3.5 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Kategori Baru</span>
                    </button>
                </div>

                @error('sections') <p class="text-red-500 text-xs bg-red-50 p-2.5 rounded-xl">{{ $message }}</p> @enderror

                {{-- Sections Loop --}}
                @foreach($sections as $secIndex => $section)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" wire:key="sec-{{ $secIndex }}">
                        {{-- Section Header Bar --}}
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
                            <div class="flex-1 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-pink-100 text-pink-700 text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ $secIndex + 1 }}
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="sections.{{ $secIndex }}.name"
                                    placeholder="Nama Kategori (cth: Wedding & Bridal Packages)"
                                    class="w-full bg-white font-bold text-sm text-gray-800 border-gray-200 rounded-lg py-1 px-2.5 focus:ring-pink-500">
                            </div>

                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="moveSectionUp({{ $secIndex }})" title="Geser ke Atas"
                                    class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="moveSectionDown({{ $secIndex }})" title="Geser ke Bawah"
                                    class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="removeSection({{ $secIndex }})" wire:confirm="Hapus kategori ini beserta seluruh paket di dalamnya?"
                                    class="p-1 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition ml-1" title="Hapus Kategori">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="p-4 sm:p-5 space-y-4">
                            <div>
                                <input type="text" wire:model.live.debounce.300ms="sections.{{ $secIndex }}.description"
                                    placeholder="Deskripsi singkat kategori (opsional, cth: Paket rias lengkap hari pernikahan)..."
                                    class="w-full text-xs text-gray-600 border-gray-200 rounded-xl focus:ring-pink-500">
                            </div>

                            {{-- Items Inside Section --}}
                            <div class="space-y-3 pt-2">
                                @forelse($section['items'] ?? [] as $itemIndex => $item)
                                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-3.5 sm:p-4 space-y-3 relative group" wire:key="item-{{ $secIndex }}-{{ $itemIndex }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <div class="sm:col-span-2">
                                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Paket *</label>
                                                    <input type="text" wire:model.live.debounce.300ms="sections.{{ $secIndex }}.items.{{ $itemIndex }}.name"
                                                        placeholder="cth: Akad & Resepsi Exclusive"
                                                        class="w-full bg-white text-xs font-semibold text-gray-900 border-gray-200 rounded-lg focus:ring-pink-500">
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Harga (Rp) *</label>
                                                    <input type="number" wire:model.live.debounce.300ms="sections.{{ $secIndex }}.items.{{ $itemIndex }}.price"
                                                        placeholder="3500000"
                                                        class="w-full bg-white text-xs font-bold text-pink-600 border-gray-200 rounded-lg focus:ring-pink-500">
                                                </div>
                                            </div>

                                            <button type="button" wire:click="removeItem({{ $secIndex }}, {{ $itemIndex }})"
                                                class="text-gray-400 hover:text-red-500 p-1 rounded-md transition" title="Hapus Paket">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div>
                                                <input type="text" wire:model.live.debounce.300ms="sections.{{ $secIndex }}.items.{{ $itemIndex }}.duration_text"
                                                    placeholder="Durasi / Waktu (cth: 3-4 Jam / 1 Sesi)"
                                                    class="w-full bg-white text-xs text-gray-600 border-gray-200 rounded-lg focus:ring-pink-500">
                                            </div>
                                            <div class="flex items-center">
                                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-gray-200 w-full">
                                                    <input type="checkbox" wire:model.live="sections.{{ $secIndex }}.items.{{ $itemIndex }}.is_highlighted"
                                                        class="rounded text-pink-600 focus:ring-pink-500 h-4 w-4">
                                                    <span class="text-xs font-semibold text-amber-700">⭐ Badge Best Seller / Recommended</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div>
                                            <input type="text" wire:model.live.debounce.300ms="sections.{{ $secIndex }}.items.{{ $itemIndex }}.description"
                                                placeholder="Keterangan singkat paket..."
                                                class="w-full bg-white text-xs text-gray-600 border-gray-200 rounded-lg focus:ring-pink-500">
                                        </div>

                                        {{-- Features / Benefit Checklist --}}
                                        <div class="pt-2 border-t border-gray-200/80">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="text-[11px] font-semibold text-gray-600 flex items-center gap-1">
                                                    <span>🎁</span>
                                                    <span>Benefit & Fasilitas Termasuk:</span>
                                                </span>
                                                <button type="button" wire:click="addFeature({{ $secIndex }}, {{ $itemIndex }})"
                                                    class="text-[11px] text-pink-600 hover:text-pink-700 font-semibold flex items-center gap-0.5">
                                                    + Tambah Poin
                                                </button>
                                            </div>

                                            <div class="space-y-1.5">
                                                @foreach($item['features'] ?? [] as $featIndex => $feat)
                                                    <div class="flex items-center gap-1.5" wire:key="feat-{{ $secIndex }}-{{ $itemIndex }}-{{ $featIndex }}">
                                                        <span class="text-green-500 text-xs">✓</span>
                                                        <input type="text" wire:model.live.debounce.300ms="sections.{{ $secIndex }}.items.{{ $itemIndex }}.features.{{ $featIndex }}"
                                                            placeholder="cth: Free Softlens, Retouch 1x..."
                                                            class="w-full bg-white text-xs text-gray-700 border-gray-200 rounded-lg py-1 px-2.5 focus:ring-pink-500">
                                                        <button type="button" wire:click="removeFeature({{ $secIndex }}, {{ $itemIndex }}, {{ $featIndex }})"
                                                            class="text-gray-400 hover:text-red-500 p-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 border border-dashed border-gray-200 rounded-xl text-xs text-gray-400">
                                        Belum ada paket di kategori ini.
                                    </div>
                                @endforelse
                            </div>

                            {{-- Bottom Item Adder & Catalog Importer --}}
                            <div class="pt-2 flex flex-wrap items-center justify-between gap-2">
                                <button type="button" wire:click="addItem({{ $secIndex }})"
                                    class="inline-flex items-center gap-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 px-3 py-2 rounded-xl text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>+ Tambah Paket Manual</span>
                                </button>

                                @if($availableServices->count() > 0)
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" type="button"
                                            class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-xl text-xs font-semibold transition">
                                            <span>📂 Impor dari Katalog Layanan</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-cloak
                                            class="absolute right-0 bottom-full mb-1 w-64 bg-white border border-gray-200 rounded-xl shadow-lg p-2 z-30 max-h-48 overflow-y-auto space-y-1">
                                            <p class="text-[11px] font-semibold text-gray-400 px-2 py-1">Pilih Layanan Studio:</p>
                                            @foreach($availableServices as $srv)
                                                <button type="button" wire:click="importServiceToItem({{ $secIndex }}, {{ $srv->id }})" @click="open = false"
                                                    class="w-full text-left px-2 py-1.5 rounded-lg text-xs hover:bg-pink-50 hover:text-pink-700 flex items-center justify-between">
                                                    <span class="font-medium truncate">{{ $srv->name }}</span>
                                                    <span class="text-[11px] font-bold text-gray-500 shrink-0">{{ $srv->formatted_price }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- TAB 3: SYARAT & KETENTUAN (T&C) --}}
            <div x-show="$wire.activeTab === 'terms'" class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 mb-1">Syarat & Ketentuan (T&C)</h3>
                        <p class="text-xs text-gray-500">Kebijakan booking, DP, jam standby, dan pembatalan.</p>
                    </div>
                    <button type="button" wire:click="resetDefaultTerms"
                        class="text-xs text-pink-600 hover:text-pink-700 font-semibold underline">
                        Gunakan Template Standar MUA
                    </button>
                </div>

                {{-- Add Term Input --}}
                <div class="flex gap-2">
                    <input type="text" wire:model="newTermInput" wire:keydown.enter.prevent="addTerm"
                        placeholder="Ketik poin aturan/kebijakan baru..."
                        class="flex-1 rounded-xl border-gray-200 text-xs sm:text-sm focus:ring-pink-500">
                    <button type="button" wire:click="addTerm"
                        class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        + Tambah
                    </button>
                </div>

                {{-- Terms List --}}
                <div class="space-y-2">
                    @forelse($terms_conditions as $termIndex => $term)
                        <div class="flex items-center gap-2 p-2.5 bg-gray-50 rounded-xl border border-gray-200" wire:key="term-{{ $termIndex }}">
                            <span class="text-gray-400 text-xs font-mono w-5 shrink-0">{{ $termIndex + 1 }}.</span>
                            <input type="text" wire:model.live.debounce.300ms="terms_conditions.{{ $termIndex }}"
                                class="flex-1 bg-transparent border-0 text-xs sm:text-sm text-gray-800 focus:ring-0 p-0 font-medium">
                            <button type="button" wire:click="removeTerm({{ $termIndex }})"
                                class="text-gray-400 hover:text-red-500 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-6">Belum ada syarat & ketentuan.</p>
                    @endforelse
                </div>
            </div>

            {{-- TAB 4: REKENING & FOOTER --}}
            <div x-show="$wire.activeTab === 'footer'" class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm space-y-5">
                <div>
                    <h3 class="text-base font-bold text-gray-800 mb-1">Catatan Penutup & Info Pembayaran</h3>
                    <p class="text-xs text-gray-500">Pesan penutup di akhir brosur pricelist dan panduan booking.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Catatan Footer / Rekening Studio</label>
                        <textarea wire:model.live.debounce.300ms="custom_footer_notes" rows="4"
                            placeholder="Pemesanan tanggal berlaku setelah konfirmasi DP. Pembayaran via Transfer: BCA 12345678 a/n Nama Studio."
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-pink-500"></textarea>
                    </div>

                    <div class="p-4 bg-pink-50/60 rounded-xl border border-pink-100 text-xs text-gray-600">
                        <p class="font-semibold text-gray-800 mb-1">💡 Tips Desain Pricelist MUA:</p>
                        <ul class="list-disc pl-4 space-y-1 text-gray-600">
                            <li>Cantumkan nomor WhatsApp yang aktif untuk memudahkan komunikasi klien.</li>
                            <li>Tandai paket yang paling diminati dengan opsi <strong>Badge Best Seller</strong> agar menarik perhatian.</li>
                            <li>Berikan rincian benefit (seperti gratis bulu mata/softlens) untuk memberikan nilai tambah yang jelas dibanding kompetitor.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        {{-- SISI KANAN: Live Realtime Preview Simulator (6 Cols on LG) --}}
        <div class="lg:col-span-6 xl:col-span-6 sticky top-20">
            <div class="bg-gray-900 rounded-3xl p-3 sm:p-5 shadow-2xl border border-gray-800 flex flex-col items-center">
                
                {{-- Preview Header info & badge --}}
                <div class="w-full flex items-center justify-between pb-3 px-2 text-xs text-gray-400 border-b border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="font-medium text-white">Live Realtime Preview</span>
                    </div>
                    <span class="uppercase font-mono text-[10px] tracking-wider text-pink-400">{{ $theme_template }}</span>
                </div>

                {{-- Preview Viewport Frame --}}
                <div class="w-full overflow-y-auto max-h-[750px] rounded-2xl bg-white shadow-inner custom-scrollbar transition-all duration-300 {{ $previewMode === 'mobile' ? 'max-w-[360px] border-4 border-gray-700' : 'w-full' }}">
                    
                    {{-- THEME CONTAINER --}}
                    @php
                        $themeBg = match($theme_template) {
                            'luxury_gold'    => 'bg-neutral-950 text-neutral-100',
                            'clean_nude'      => 'bg-amber-50/50 text-stone-800',
                            'sage_botanical' => 'bg-emerald-50/40 text-emerald-950',
                            default          => 'bg-white text-gray-900',
                        };

                        $cardBg = match($theme_template) {
                            'luxury_gold'    => 'bg-neutral-900/90 border-amber-900/40 text-neutral-100',
                            'clean_nude'      => 'bg-white border-amber-200/70 text-stone-800 shadow-sm',
                            'sage_botanical' => 'bg-white border-emerald-200/70 text-emerald-950 shadow-sm',
                            default          => 'bg-white border-pink-100 text-gray-900 shadow-sm',
                        };

                        $subtextCol = match($theme_template) {
                            'luxury_gold'    => 'text-neutral-400',
                            'clean_nude'      => 'text-stone-500',
                            'sage_botanical' => 'text-emerald-700/80',
                            default          => 'text-gray-500',
                        };
                    @endphp

                    <div class="p-5 sm:p-7 {{ $themeBg }} font-sans min-h-[500px]">
                        
                        {{-- Top Branding Header --}}
                        <div class="text-center pb-6 border-b {{ $theme_template === 'luxury_gold' ? 'border-neutral-800' : 'border-gray-200/70' }}">
                            @if($show_logo && $user->invoice_logo_path)
                                <img src="{{ asset('storage/' . $user->invoice_logo_path) }}" alt="Logo" class="h-14 mx-auto mb-3 object-contain rounded-xl">
                            @endif

                            <h2 class="text-xl sm:text-2xl font-serif font-bold tracking-wide" style="color: {{ $primary_color }};">
                                {{ $user->studio_name ?: $user->name }}
                            </h2>

                            <h3 class="text-sm font-semibold tracking-wider uppercase mt-1">
                                {{ $title ?: 'Pricelist Layanan' }}
                            </h3>

                            @if($description)
                                <p class="text-xs {{ $subtextCol }} max-w-md mx-auto mt-2 leading-relaxed italic">
                                    "{{ $description }}"
                                </p>
                            @endif

                            @if($show_social_media && ($user->instagram || $user->tiktok))
                                <div class="flex items-center justify-center gap-3 mt-3 text-[11px] {{ $subtextCol }}">
                                    @if($user->instagram)
                                        <span>📷 @ {{ $user->instagram }}</span>
                                    @endif
                                    @if($user->tiktok)
                                        <span>🎵 @ {{ $user->tiktok }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Sections & Packages --}}
                        <div class="py-6 space-y-7">
                            @foreach($sections as $sec)
                                <div class="space-y-4">
                                    {{-- Section Title & Decoration --}}
                                    <div class="text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="h-px w-8" style="background-color: {{ $primary_color }};"></span>
                                            <h4 class="font-serif font-bold text-sm sm:text-base uppercase tracking-wider" style="color: {{ $primary_color }};">
                                                {{ $sec['name'] }}
                                            </h4>
                                            <span class="h-px w-8" style="background-color: {{ $primary_color }};"></span>
                                        </div>
                                        @if(!empty($sec['description']))
                                    <p class="text-[11px] {{ $subtextCol }} mt-0.5">{{ $sec['description'] }}</p>
                                        @endif
                                    </div>

                                    {{-- Packages Grid inside Section --}}
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach($sec['items'] ?? [] as $it)
                                            <div class="rounded-2xl border p-4 transition duration-200 relative {{ $cardBg }} {{ !empty($it['is_highlighted']) ? 'ring-2 ring-offset-1' : '' }}"
                                                style="{{ !empty($it['is_highlighted']) ? 'ring-color: ' . $primary_color . ';' : '' }}">
                                                
                                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                                    <div class="flex-1">
                                                        <div class="flex flex-wrap items-center gap-1.5">
                                                            <h5 class="font-bold text-sm leading-snug">{{ $it['name'] }}</h5>
                                                            @if(!empty($it['is_highlighted']))
                                                                <span class="inline-flex items-center gap-0.5 text-[9px] font-bold uppercase tracking-wider text-white px-2 py-0.5 rounded-full shadow-sm"
                                                                    style="background-color: {{ $primary_color }};">
                                                                    ⭐ Best Seller
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if(!empty($it['duration_text']))
                                                            <span class="text-[10px] {{ $subtextCol }} block mt-0.5">⏱ {{ $it['duration_text'] }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="font-bold text-sm sm:text-base shrink-0" style="color: {{ $primary_color }};">
                                                        Rp {{ number_format((float)($it['price'] ?? 0), 0, ',', '.') }}
                                                    </span>
                                                </div>

                                                @if(!empty($it['description']))
                                                    <p class="text-xs {{ $subtextCol }} mb-2.5 leading-relaxed">{{ $it['description'] }}</p>
                                                @endif

                                                {{-- Checklist Benefit --}}
                                                @if(!empty($it['features']) && count($it['features']) > 0)
                                                    <div class="pt-2 border-t {{ $theme_template === 'luxury_gold' ? 'border-neutral-800' : 'border-gray-100' }} space-y-1">
                                                        @foreach($it['features'] as $feat)
                                                            <div class="flex items-center gap-1.5 text-xs">
                                                                <span class="font-bold text-xs" style="color: {{ $primary_color }};">✓</span>
                                                                <span class="text-[11px] leading-tight">{{ $feat }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Terms & Conditions Section --}}
                        @if(!empty($terms_conditions) && count($terms_conditions) > 0)
                            <div class="pt-5 pb-5 border-t {{ $theme_template === 'luxury_gold' ? 'border-neutral-800' : 'border-gray-200/70' }}">
                                <h4 class="font-serif font-bold text-xs uppercase tracking-wider mb-2 text-center" style="color: {{ $primary_color }};">
                                    Syarat & Ketentuan (Terms & Conditions)
                                </h4>
                                <ul class="space-y-1 text-[11px] {{ $subtextCol }} list-disc pl-4 leading-relaxed">
                                    @foreach($terms_conditions as $t)
                                        <li>{{ $t }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Footer & Contact --}}
                        <div class="pt-5 border-t {{ $theme_template === 'luxury_gold' ? 'border-neutral-800' : 'border-gray-200/70' }} text-center space-y-3">
                            @if($custom_footer_notes)
                                <p class="text-[11px] {{ $subtextCol }} leading-relaxed">
                                    {{ $custom_footer_notes }}
                                </p>
                            @endif

                            @if($show_contact_button && $user->phone)
                                <div class="pt-2">
                                    <div class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md cursor-pointer"
                                        style="background-color: {{ $primary_color }};">
                                        <span>💬 Booking via WhatsApp ({{ $user->phone }})</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
