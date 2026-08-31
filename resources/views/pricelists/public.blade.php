<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO & Open Graph Metadata --}}
    <title>{{ $pricelist->title }} - {{ $pricelist->user->studio_name ?: $pricelist->user->name }}</title>
    <meta name="description" content="{{ $pricelist->description ?: 'Brosur paket rias dan harga layanan eksklusif dari ' . ($pricelist->user->studio_name ?: $pricelist->user->name) }}">
    
    {{-- Open Graph / WhatsApp / Facebook Preview --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pricelist->title }} - {{ $pricelist->user->studio_name ?: $pricelist->user->name }}">
    <meta property="og:description" content="{{ $pricelist->description ?: 'Brosur paket rias dan harga layanan eksklusif dari ' . ($pricelist->user->studio_name ?: $pricelist->user->name) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($pricelist->user->invoice_logo_path)
        <meta property="og:image" content="{{ asset('storage/' . $pricelist->user->invoice_logo_path) }}">
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|playfair-display:600,700,800&display=swap" rel="stylesheet" />

    {{-- Tailwind / Vite Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $primary = $pricelist->primary_color ?: '#ec4899';
        $theme = $pricelist->theme_template ?: 'rose_blush';

        $bodyClass = match($theme) {
            'luxury_gold'    => 'bg-neutral-950 text-neutral-100 selection:bg-amber-500 selection:text-black',
            'clean_nude'      => 'bg-[#fdfaf7] text-stone-800 selection:bg-amber-600 selection:text-white',
            'sage_botanical' => 'bg-[#f4f9f5] text-emerald-950 selection:bg-emerald-600 selection:text-white',
            default          => 'bg-[#fffafd] text-gray-900 selection:bg-pink-500 selection:text-white',
        };

        $cardClass = match($theme) {
            'luxury_gold'    => 'bg-neutral-900/90 border-neutral-800 text-neutral-100 hover:border-amber-500/50',
            'clean_nude'      => 'bg-white border-amber-200/60 text-stone-800 shadow-sm hover:border-amber-400',
            'sage_botanical' => 'bg-white border-emerald-200/60 text-emerald-950 shadow-sm hover:border-emerald-400',
            default          => 'bg-white border-pink-100 text-gray-900 shadow-sm hover:border-pink-300',
        };

        $subtextClass = match($theme) {
            'luxury_gold'    => 'text-neutral-400',
            'clean_nude'      => 'text-stone-500',
            'sage_botanical' => 'text-emerald-700/80',
            default          => 'text-gray-500',
        };

        $dividerClass = match($theme) {
            'luxury_gold'    => 'border-neutral-800',
            'clean_nude'      => 'border-amber-200/60',
            'sage_botanical' => 'border-emerald-200/60',
            default          => 'border-pink-100',
        };
    @endphp
</head>
<body class="{{ $bodyClass }} font-sans antialiased min-h-screen flex flex-col justify-between">

    {{-- Main Container --}}
    <main class="w-full max-w-2xl mx-auto px-4 py-8 sm:py-12">

        {{-- Top Floating Quick Actions (Download PDF & Share) --}}
        <div class="flex items-center justify-between gap-2 mb-6">
            <a href="{{ route('pricelists.public-pdf', $pricelist->slug) }}" target="_blank"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white/80 dark:bg-neutral-800/80 backdrop-blur border border-gray-200 dark:border-neutral-700 shadow-sm hover:bg-white transition">
                <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh PDF</span>
            </a>

            @if($pricelist->show_contact_button && $pricelist->user->phone)
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $pricelist->user->phone);
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $generalWaMsg = rawurlencode("Halo " . ($pricelist->user->studio_name ?: $pricelist->user->name) . ", saya ingin konsultasi mengenai " . $pricelist->title . ".");
                @endphp
                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $generalWaMsg }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition active:scale-95"
                    style="background-color: {{ $primary }};">
                    <span>💬 Chat WhatsApp</span>
                </a>
            @endif
        </div>

        {{-- Header Branding Box --}}
        <header class="text-center pb-8 border-b {{ $dividerClass }}">
            @if($pricelist->show_logo && $pricelist->user->invoice_logo_path)
                <div class="mb-4">
                    <img src="{{ asset('storage/' . $pricelist->user->invoice_logo_path) }}" 
                         alt="{{ $pricelist->user->studio_name ?: $pricelist->user->name }}" 
                         class="h-20 sm:h-24 mx-auto object-contain rounded-2xl shadow-sm">
                </div>
            @endif

            <h1 class="font-serif text-2xl sm:text-3xl font-bold tracking-tight" style="color: {{ $primary }};">
                {{ $pricelist->user->studio_name ?: $pricelist->user->name }}
            </h1>

            <h2 class="text-base sm:text-lg font-semibold tracking-wide uppercase mt-2">
                {{ $pricelist->title }}
            </h2>

            @if($pricelist->description)
                <p class="text-xs sm:text-sm {{ $subtextClass }} max-w-lg mx-auto mt-2 leading-relaxed italic">
                    "{{ $pricelist->description }}"
                </p>
            @endif

            {{-- Social Media Links --}}
            @if($pricelist->show_social_media && ($pricelist->user->instagram || $pricelist->user->tiktok))
                <div class="flex items-center justify-center gap-4 mt-4 text-xs {{ $subtextClass }}">
                    @if($pricelist->user->instagram)
                        <a href="https://instagram.com/{{ ltrim($pricelist->user->instagram, '@') }}" target="_blank" 
                           class="inline-flex items-center gap-1 hover:underline">
                            <span>📷</span>
                            <span>@ {{ ltrim($pricelist->user->instagram, '@') }}</span>
                        </a>
                    @endif
                    @if($pricelist->user->tiktok)
                        <a href="https://tiktok.com/@ {{ ltrim($pricelist->user->tiktok, '@') }}" target="_blank" 
                           class="inline-flex items-center gap-1 hover:underline">
                            <span>🎵</span>
                            <span>@ {{ ltrim($pricelist->user->tiktok, '@') }}</span>
                        </a>
                    @endif
                </div>
            @endif
        </header>

        {{-- Sections & Packages --}}
        <div class="py-8 space-y-10">
            @foreach($pricelist->sections as $section)
                <section class="space-y-4">
                    {{-- Section Title --}}
                    <div class="text-center">
                        <div class="inline-flex items-center gap-3">
                            <span class="h-px w-8 sm:w-12" style="background-color: {{ $primary }};"></span>
                            <h3 class="font-serif font-bold text-base sm:text-lg uppercase tracking-wider" style="color: {{ $primary }};">
                                {{ $section->name }}
                            </h3>
                            <span class="h-px w-8 sm:w-12" style="background-color: {{ $primary }};"></span>
                        </div>
                        @if($section->description)
                            <p class="text-xs {{ $subtextClass }} mt-1">{{ $section->description }}</p>
                        @endif
                    </div>

                    {{-- Packages Cards --}}
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($section->items as $item)
                            <div class="rounded-2xl border p-5 transition duration-200 relative {{ $cardClass }} {{ $item->is_highlighted ? 'ring-2' : '' }}"
                                style="{{ $item->is_highlighted ? 'ring-color: ' . $primary . ';' : '' }}">
                                
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-bold text-base sm:text-lg leading-snug">{{ $item->name }}</h4>
                                            @if($item->is_highlighted)
                                                <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-white px-2.5 py-0.5 rounded-full shadow-sm"
                                                    style="background-color: {{ $primary }};">
                                                    ⭐ Best Seller
                                                </span>
                                            @endif
                                        </div>
                                        @if($item->duration_text)
                                            <span class="text-xs {{ $subtextClass }} block mt-1">⏱ Durasi: {{ $item->duration_text }}</span>
                                        @endif
                                    </div>
                                    <div class="sm:text-right shrink-0 mt-1 sm:mt-0">
                                        <span class="font-bold text-lg sm:text-xl block" style="color: {{ $primary }};">
                                            {{ $item->formatted_price }}
                                        </span>
                                    </div>
                                </div>

                                @if($item->description)
                                    <p class="text-xs sm:text-sm {{ $subtextClass }} mb-3 leading-relaxed">{{ $item->description }}</p>
                                @endif

                                {{-- Features Checklist --}}
                                @if(!empty($item->features) && count($item->features) > 0)
                                    <div class="pt-3 border-t {{ $dividerClass }} space-y-1.5 mb-4">
                                        <p class="text-[11px] font-semibold uppercase tracking-wider {{ $subtextClass }} mb-1">Benefit & Fasilitas:</p>
                                        @foreach($item->features as $feature)
                                            <div class="flex items-center gap-2 text-xs sm:text-sm">
                                                <span class="font-bold text-sm" style="color: {{ $primary }};">✓</span>
                                                <span class="leading-tight">{{ $feature }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Booking CTA per Item --}}
                                @if($pricelist->show_contact_button && $pricelist->user->phone)
                                    @php
                                        $waItemMsg = rawurlencode("Halo " . ($pricelist->user->studio_name ?: $pricelist->user->name) . ", saya ingin booking paket *" . $item->name . "* seharga *" . $item->formatted_price . "* dari " . $pricelist->title . ". Apakah tanggal [isi tanggal acara] masih tersedia?");
                                    @endphp
                                    <div class="pt-3 border-t {{ $dividerClass }} flex items-center justify-end">
                                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waItemMsg }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white shadow transition hover:opacity-95 active:scale-95"
                                            style="background-color: {{ $primary }};">
                                            <span>Pesan Paket Ini</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        {{-- Terms & Conditions Section --}}
        @if(!empty($pricelist->terms_conditions) && count($pricelist->terms_conditions) > 0)
            <section class="pt-6 pb-6 border-t {{ $dividerClass }}">
                <h4 class="font-serif font-bold text-sm sm:text-base uppercase tracking-wider mb-3 text-center" style="color: {{ $primary }};">
                    Syarat & Ketentuan Booking
                </h4>
                <div class="rounded-2xl border p-4 sm:p-5 {{ $cardClass }}">
                    <ol class="space-y-2 text-xs sm:text-sm {{ $subtextClass }} list-decimal pl-4 leading-relaxed">
                        @foreach($pricelist->terms_conditions as $term)
                            <li>{{ $term }}</li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        {{-- Footer & Contact --}}
        <footer class="pt-6 border-t {{ $dividerClass }} text-center space-y-4">
            @if($pricelist->custom_footer_notes)
                <p class="text-xs sm:text-sm {{ $subtextClass }} leading-relaxed">
                    {{ $pricelist->custom_footer_notes }}
                </p>
            @endif

            @if($pricelist->show_contact_button && $pricelist->user->phone)
                <div class="pt-2">
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $generalWaMsg }}" target="_blank"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3 rounded-2xl text-sm font-bold text-white shadow-lg transition active:scale-95"
                        style="background-color: {{ $primary }};">
                        <span>💬 Hubungi WhatsApp Studio ({{ $pricelist->user->phone }})</span>
                    </a>
                </div>
            @endif

            <p class="text-[11px] text-gray-400 pt-4">
                Powered by 💄 <a href="{{ url('/') }}" class="font-semibold text-pink-600 hover:underline">MUA Manager</a>
            </p>
        </footer>

    </main>

</body>
</html>
