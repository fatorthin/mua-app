<div x-data="{
    open: false,
    invoiceNumber: '',
    previewUrl: '',
    downloadUrl: '',
    loading: true,
    hasError: false,
    downloading: false,
    openModal(data) {
        this.invoiceNumber = data.number || 'Invoice';
        this.previewUrl = data.previewUrl || '';
        this.downloadUrl = data.downloadUrl || '';
        this.loading = true;
        this.hasError = false;
        this.downloading = false;
        this.open = true;

        // Push state to browser history so pressing Android/mobile Back button closes the modal without exiting the app
        window.history.pushState({ modal: 'invoice-preview' }, '');
    },
    async downloadPdf() {
        if (!this.downloadUrl || this.downloading) return;
        this.downloading = true;
        try {
            await window.downloadPdfInvoice(this.downloadUrl, 'Invoice-' + this.invoiceNumber + '.pdf');
        } finally {
            this.downloading = false;
        }
    },
    retryLoad() {
        this.hasError = false;
        this.loading = true;
        const current = this.previewUrl;
        this.previewUrl = '';
        this.$nextTick(() => {
            this.previewUrl = current;
        });
    },
    closeModal(fromPopState = false) {
        if (!this.open) return;
        this.open = false;
        this.previewUrl = '';
        if (!fromPopState && window.history.state && window.history.state.modal === 'invoice-preview') {
            window.history.back();
        }
    }
}"
x-on:open-invoice-preview.window="openModal($event.detail)"
x-on:popstate.window="if (open && (!window.history.state || window.history.state.modal !== 'invoice-preview')) { closeModal(true); }"
x-on:keydown.escape.window="closeModal()"
x-show="open"
x-cloak
class="fixed inset-0 z-50 overflow-y-auto"
aria-labelledby="modal-title"
role="dialog"
aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
         x-on:click="closeModal()"></div>

    <div class="flex min-h-full items-end justify-center p-2 text-center sm:items-center sm:p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl flex flex-col max-h-[92vh]">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 bg-white sticky top-0 z-10">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="p-2 bg-pink-50 rounded-xl text-pink-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 truncate" x-text="'Preview ' + invoiceNumber"></h3>
                        <p class="text-xs text-gray-500">Tekan tombol kembali di HP untuk menutup</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="downloadPdf()"
                            :disabled="downloading"
                            class="inline-flex items-center gap-1.5 bg-pink-600 hover:bg-pink-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition-colors disabled:opacity-75">
                        <template x-if="!downloading">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </template>
                        <template x-if="downloading">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span x-text="downloading ? 'Mengunduh...' : 'Download PDF'"></span>
                    </button>
                    <button type="button"
                            x-on:click="closeModal()"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none transition-colors"
                            aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="flex-1 overflow-y-auto p-2 sm:p-4 bg-gray-100 flex flex-col items-center justify-center min-h-[420px]">
                {{-- Loading Spinner --}}
                <div x-show="loading" class="flex flex-col items-center justify-center py-16 text-gray-500">
                    <svg class="animate-spin h-8 w-8 text-pink-600 mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <p class="text-xs font-medium text-gray-600">Memuat pratinjau invoice...</p>
                </div>

                {{-- Iframe HTML Preview --}}
                <div x-show="!hasError"
                     class="w-full bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden transition-opacity duration-200"
                     :class="loading ? 'opacity-0 h-0' : 'opacity-100 h-[65vh] sm:h-[72vh] min-h-[420px]'">
                    <iframe :src="previewUrl"
                            x-on:load="loading = false"
                            x-on:error="loading = false; hasError = true"
                            class="w-full h-full border-0 bg-white"
                            title="Pratinjau Invoice"></iframe>
                </div>

                {{-- Fallback if error --}}
                <div x-show="hasError && !loading" class="text-center py-12 px-4 bg-white rounded-xl shadow-sm border border-gray-200 w-full max-w-md">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-600 mb-3">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-800 font-semibold mb-1">Pratinjau belum dapat ditampilkan</p>
                    <p class="text-xs text-gray-500 mb-4">Anda tetap dapat mengunduh dokumen file PDF secara langsung.</p>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <button type="button"
                                x-on:click="retryLoad()"
                                class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Coba Muat Ulang
                        </button>
                        <button type="button"
                                @click="downloadPdf()"
                                :disabled="downloading"
                                class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors disabled:opacity-75">
                            <template x-if="!downloading">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </template>
                            <template x-if="downloading">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span x-text="downloading ? 'Mengunduh PDF...' : 'Unduh File PDF'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3 bg-white text-xs text-gray-500">
                <span class="flex items-center gap-1.5 font-medium text-emerald-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Pratinjau Resmi MUA
                </span>
                <button type="button"
                        x-on:click="closeModal()"
                        class="text-gray-600 font-medium hover:text-gray-900 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
