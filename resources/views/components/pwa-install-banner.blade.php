<div x-data="{
        showBanner: false,
        deferredPrompt: null,
        init() {
            // Check if dismissed recently (e.g. within 7 days)
            const dismissedAt = localStorage.getItem('pwa_banner_dismissed_at');
            if (dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < 7 * 24 * 60 * 60 * 1000) {
                return;
            }

            // Check if already in standalone mode (installed)
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                return;
            }

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                this.showBanner = true;
            });
        },
        async install() {
            if (!this.deferredPrompt) return;
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                this.showBanner = false;
            }
            this.deferredPrompt = null;
        },
        dismiss() {
            this.showBanner = false;
            localStorage.setItem('pwa_banner_dismissed_at', Date.now().toString());
        }
    }" 
    x-show="showBanner"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed bottom-20 sm:bottom-6 left-4 right-4 sm:left-auto sm:right-6 sm:max-w-md z-40"
    x-cloak>
    <div class="rounded-2xl bg-white/95 backdrop-blur-lg p-4 shadow-[0_10px_35px_rgba(236,72,153,0.2)] border border-pink-100 ring-1 ring-pink-50 flex items-start gap-3">
        <div class="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center text-2xl shrink-0 shadow-inner">
            💄
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-gray-900 leading-tight">Pasang MUA Manager di HP</h4>
            <p class="text-xs text-gray-500 mt-1 leading-normal">Buka aplikasi lebih cepat langsung dari layar utama tanpa ketik browser.</p>
            <div class="flex items-center gap-2 mt-3">
                <button @click="install()" type="button"
                    class="px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-semibold shadow-sm active:scale-95 transition-all">
                    Pasang Sekarang
                </button>
                <button @click="dismiss()" type="button"
                    class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-medium active:scale-95 transition-all">
                    Nanti Saja
                </button>
            </div>
        </div>
        <button @click="dismiss()" class="text-gray-400 hover:text-gray-600 p-1 -mr-1 -mt-1 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
