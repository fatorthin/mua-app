@php
    $isDashboard = request()->routeIs('dashboard');
    $isCalendar  = request()->routeIs('bookings.calendar');
    $isInvoice   = request()->routeIs('invoices.*');
    $isClients   = request()->routeIs('clients.*');
    $isCreate    = request()->routeIs('bookings.create');
@endphp

<nav class="fixed bottom-0 inset-x-0 z-30 sm:hidden bg-white/95 backdrop-blur-md border-t border-pink-100/80 shadow-[0_-4px_20px_rgba(236,72,153,0.08)] pb-[env(safe-area-inset-bottom,0px)]">
    <div class="grid grid-cols-5 items-center h-16 px-2 max-w-lg mx-auto relative">
        
        {{-- 1. Dashboard --}}
        <a href="{{ route('dashboard') }}" wire:navigate 
           class="flex flex-col items-center justify-center py-1 group transition-colors {{ $isDashboard ? 'text-pink-600' : 'text-gray-500 hover:text-gray-900' }}">
            <div class="relative">
                <svg class="w-5 h-5 transition-transform group-active:scale-90" fill="{{ $isDashboard ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                @if ($isDashboard)
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-pink-600 rounded-full"></span>
                @endif
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $isDashboard ? 'font-semibold text-pink-600' : 'text-gray-500' }}">Home</span>
        </a>

        {{-- 2. Kalender --}}
        <a href="{{ route('bookings.calendar') }}" wire:navigate 
           class="flex flex-col items-center justify-center py-1 group transition-colors {{ $isCalendar ? 'text-pink-600' : 'text-gray-500 hover:text-gray-900' }}">
            <div class="relative">
                <svg class="w-5 h-5 transition-transform group-active:scale-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M8 7V3m8 4V3m-9 8h10m-2 9h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h2m4-6v6m-3-3h6" />
                </svg>
                @if ($isCalendar)
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-pink-600 rounded-full"></span>
                @endif
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $isCalendar ? 'font-semibold text-pink-600' : 'text-gray-500' }}">Kalender</span>
        </a>

        {{-- 3. Center FAB: Quick Add Booking --}}
        <div class="flex items-center justify-center -mt-6">
            <a href="{{ route('bookings.create') }}" wire:navigate 
               class="flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-tr from-pink-600 to-pink-500 text-white shadow-lg shadow-pink-500/40 border-4 border-white active:scale-90 active:shadow-sm transition-all duration-150"
               aria-label="Tambah Booking">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
            </a>
        </div>

        {{-- 4. Invoice --}}
        <a href="{{ route('invoices.index') }}" wire:navigate 
           class="flex flex-col items-center justify-center py-1 group transition-colors {{ $isInvoice ? 'text-pink-600' : 'text-gray-500 hover:text-gray-900' }}">
            <div class="relative">
                <svg class="w-5 h-5 transition-transform group-active:scale-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                @if ($isInvoice)
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-pink-600 rounded-full"></span>
                @endif
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $isInvoice ? 'font-semibold text-pink-600' : 'text-gray-500' }}">Invoice</span>
        </a>

        {{-- 5. Klien --}}
        <a href="{{ route('clients.index') }}" wire:navigate 
           class="flex flex-col items-center justify-center py-1 group transition-colors {{ $isClients ? 'text-pink-600' : 'text-gray-500 hover:text-gray-900' }}">
            <div class="relative">
                <svg class="w-5 h-5 transition-transform group-active:scale-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                @if ($isClients)
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-pink-600 rounded-full"></span>
                @endif
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $isClients ? 'font-semibold text-pink-600' : 'text-gray-500' }}">Klien</span>
        </a>

    </div>
</nav>
