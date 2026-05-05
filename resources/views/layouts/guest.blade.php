<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MUA Manager') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ec4899">
    <link rel="icon" type="image/png" href="/lip-matt.png">
    <link rel="apple-touch-icon" href="/lip-matt.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[linear-gradient(180deg,#fff7fb_0%,#ffffff_45%,#fff1f6_100%)] text-gray-900">
    <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto flex min-h-[calc(100vh-3rem)] max-w-6xl overflow-hidden rounded-[2rem] bg-white shadow-[0_30px_80px_rgba(236,72,153,0.12)]">
            <div class="hidden lg:flex lg:w-1/2 flex-col justify-between bg-[radial-gradient(circle_at_top_left,_rgba(244,114,182,0.22),_transparent_35%),linear-gradient(180deg,#fff7fb_0%,#ffe4ef_100%)] p-10">
                <div>
                    <a href="/" wire:navigate class="inline-flex items-center gap-3 rounded-full bg-white/70 px-4 py-2 text-sm font-semibold text-pink-600 shadow-sm ring-1 ring-pink-100 backdrop-blur">
                        <span class="text-xl">💄</span>
                        <span>MUA Manager</span>
                    </a>
                    <div class="mt-12 max-w-md">
                        <p class="inline-flex rounded-full bg-pink-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-pink-700">Platform MUA Indonesia</p>
                        <h1 class="mt-6 text-4xl font-bold leading-tight text-gray-900">Kelola booking, klien, dan invoice dalam satu alur kerja yang rapi.</h1>
                        <p class="mt-5 text-base leading-7 text-gray-600">Masuk ke dashboard untuk mengatur jadwal makeup, meninjau invoice, dan menjaga operasional bisnis tetap profesional.</p>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="rounded-2xl bg-white/80 p-5 ring-1 ring-pink-100 backdrop-blur">
                        <p class="text-sm font-semibold text-gray-800">Fitur utama</p>
                        <div class="mt-3 grid gap-3 text-sm text-gray-600">
                            <p>Booking management yang cepat dan anti bentrok.</p>
                            <p>Invoice otomatis yang siap kirim ke klien.</p>
                            <p>Tampilan mobile-first agar mudah dipakai saat kerja lapangan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex w-full lg:w-1/2 items-center justify-center bg-white px-5 py-8 sm:px-8 lg:px-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 lg:hidden">
                        <a href="/" wire:navigate class="inline-flex items-center gap-3 rounded-full bg-pink-50 px-4 py-2 text-sm font-semibold text-pink-600 ring-1 ring-pink-100">
                            <span class="text-xl">💄</span>
                            <span>MUA Manager</span>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
