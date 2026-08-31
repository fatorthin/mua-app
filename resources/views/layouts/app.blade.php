<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MUA Manager') }}</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ec4899">
    <link rel="icon" type="image/png" href="/lip-matt.png">
    <link rel="apple-touch-icon" href="/lip-matt.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <livewire:layout.navigation />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar -->
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    {{-- Mobile sidebar toggle --}}
                    <button @click="sidebarOpen = !sidebarOpen" class="sm:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-pink-500" aria-label="Toggle Navigation">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    @if (isset($header))
                        {{ $header }}
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
                    <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 pb-24 sm:p-6 sm:pb-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile PWA Bottom Navigation --}}
    <x-bottom-navigation />

    {{-- PWA Install Prompt Banner --}}
    <x-pwa-install-banner />

    {{-- Global In-App Invoice Preview Modal --}}
    <x-invoice-preview-modal />

    {{-- Offline Toast Indicator --}}
    <div x-data="{ isOnline: navigator.onLine }"
         @online.window="isOnline = true"
         @offline.window="isOnline = false"
         x-show="!isOnline"
         x-transition
         class="fixed top-2 inset-x-4 max-w-sm mx-auto z-50 rounded-xl bg-gray-900/90 text-white text-xs px-3 py-2 text-center backdrop-blur shadow-lg flex items-center justify-center gap-2"
         x-cloak>
        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
        <span>Mode Offline: Menampilkan data cache.</span>
    </div>

    @livewireScripts
    <script>
        window.downloadPdfInvoice = async function(url, filename) {
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/pdf' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const blob = await res.blob();
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = blobUrl;
                a.download = filename || 'Invoice.pdf';
                document.body.appendChild(a);
                a.click();
                setTimeout(() => {
                    window.URL.revokeObjectURL(blobUrl);
                    a.remove();
                }, 2000);
            } catch (err) {
                console.error('Blob download failed, fallback to direct url:', err);
                window.location.href = url;
            }
        };

        window.copyToClipboard = async function(text) {
            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(text);
                    return true;
                } catch (e) {
                    // fallback below
                }
            }
            try {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                textArea.setAttribute("readonly", "");
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                return successful;
            } catch (err) {
                console.error('Fallback clipboard copy failed:', err);
                return false;
            }
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then((reg) => {
                    reg.update();
                }).catch(err => {
                    console.log('ServiceWorker registration error:', err);
                });
            });
        }
    </script>
</body>

</html>
