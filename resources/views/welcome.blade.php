<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MUA Manager — Platform Digital untuk Makeup Artist Indonesia</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ec4899">
    <link rel="icon" type="image/png" href="/lip-matt.png">
    <link rel="apple-touch-icon" href="/lip-matt.png">
    <script>
        // Tangkap beforeinstallprompt sedini mungkin — sebelum module JS dimuat
        window.__pwaInstallEvent = null;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            window.__pwaInstallEvent = e;
            var btn = document.getElementById('pwa-install-btn');
            if (btn) btn.classList.remove('hidden');
        });
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans bg-white text-gray-800">
    <nav class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
        <div class="flex items-center gap-2 text-xl font-bold text-pink-600">
            <span>💄</span> MUA Manager
        </div>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-gray-600 text-sm hover:text-gray-900">Masuk</a>
                <a href="{{ route('register') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-pink-700">Daftar Gratis</a>
            @endauth
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6">
        <!-- Hero -->
        <section class="text-center py-20">
            <div class="inline-block bg-pink-50 text-pink-600 text-sm font-medium px-4 py-1.5 rounded-full mb-6">
                Platform #1 untuk MUA Indonesia 🇮🇩
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-5 leading-tight">
                Kelola Booking & Invoice<br>
                <span class="text-pink-600">Lebih Mudah & Profesional</span>
            </h1>
            <p class="text-lg text-gray-500 mb-8 max-w-2xl mx-auto">
                Hentikan kekacauan WhatsApp dan spreadsheet. MUA Manager hadir sebagai sistem terpadu untuk booking, jadwal, klien, dan invoice — semua dalam satu aplikasi.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('register') }}" class="bg-pink-600 text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-pink-700 text-center">Mulai Gratis Sekarang</a>
                <a href="{{ route('login') }}" class="border border-gray-200 text-gray-700 px-8 py-3.5 rounded-xl font-semibold hover:bg-gray-50 text-center">Masuk ke Akun</a>
                <button id="pwa-install-btn" type="button" class="hidden border border-pink-200 text-pink-700 px-8 py-3.5 rounded-xl font-semibold hover:bg-pink-50 text-center">
                    Install App di Perangkat Ini
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-4">Demo: rina@muamanager.id / password</p>
        </section>

        <!-- Features -->
        <section class="py-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-pink-50 rounded-2xl p-6">
                <div class="text-3xl mb-3">📅</div>
                <h3 class="font-bold text-gray-800 mb-2">Manajemen Booking</h3>
                <p class="text-sm text-gray-500">Buat, konfirmasi, dan kelola semua booking. Cegah double-booking secara otomatis.</p>
            </div>
            <div class="bg-purple-50 rounded-2xl p-6">
                <div class="text-3xl mb-3">🧾</div>
                <h3 class="font-bold text-gray-800 mb-2">Invoice Otomatis</h3>
                <p class="text-sm text-gray-500">Invoice PDF profesional dibuat otomatis setiap booking. Download langsung, kirim ke klien.</p>
            </div>
            <div class="bg-blue-50 rounded-2xl p-6">
                <div class="text-3xl mb-3">👥</div>
                <h3 class="font-bold text-gray-800 mb-2">Manajemen Klien</h3>
                <p class="text-sm text-gray-500">Simpan data klien, lihat riwayat booking, dan bangun hubungan jangka panjang.</p>
            </div>
            <div class="bg-green-50 rounded-2xl p-6">
                <div class="text-3xl mb-3">💰</div>
                <h3 class="font-bold text-gray-800 mb-2">Laporan Pendapatan</h3>
                <p class="text-sm text-gray-500">Pantau pendapatan bulanan dan tren booking dari dashboard yang intuitif.</p>
            </div>
            <div class="bg-yellow-50 rounded-2xl p-6">
                <div class="text-3xl mb-3">💄</div>
                <h3 class="font-bold text-gray-800 mb-2">Kelola Layanan</h3>
                <p class="text-sm text-gray-500">Tambah layanan dengan harga & durasi. Bridal, party, photoshoot — semuanya terorganisir.</p>
            </div>
            <div class="bg-teal-50 rounded-2xl p-6">
                <div class="text-3xl mb-3">📱</div>
                <h3 class="font-bold text-gray-800 mb-2">PWA Mobile-First</h3>
                <p class="text-sm text-gray-500">Instal seperti aplikasi di HP. Akses offline, cepat, dan tidak perlu download dari Play Store.</p>
            </div>
        </section>

        <!-- CTA -->
        <section class="text-center py-16 bg-pink-600 rounded-3xl mb-20 px-6">
            <h2 class="text-3xl font-bold text-white mb-3">Siap Kelola Bisnis MUA-mu?</h2>
            <p class="text-pink-100 mb-6">Bergabunglah dengan ribuan MUA profesional di Indonesia.</p>
            <a href="{{ route('register') }}" class="bg-white text-pink-600 px-8 py-3.5 rounded-xl font-bold hover:bg-pink-50 inline-block">
                Daftar Gratis — Mulai Sekarang
            </a>
        </section>
    </main>

    <footer class="border-t border-gray-100 py-8 text-center text-sm text-gray-400">
        © {{ date('Y') }} MUA Manager. Dibuat dengan 💄 untuk MUA Indonesia.
    </footer>
</body>

</html>
