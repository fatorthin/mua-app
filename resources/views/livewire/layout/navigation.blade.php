<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

{{-- Sidebar Navigation --}}
<aside x-data="{ mobileOpen: false }" @toggle-sidebar.window="mobileOpen = !mobileOpen" class="relative">

    {{-- Sidebar --}}
    <div :class="mobileOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 sm:static sm:translate-x-0 sm:flex sm:inset-auto">

        {{-- Logo --}}
        <div class="h-16 flex items-center px-6 border-b border-gray-200 shrink-0">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                <span class="text-2xl">💄</span>
                <span class="font-bold text-lg text-pink-600">MUA Manager</span>
            </a>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-pink-50 text-pink-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('bookings.index') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('bookings.index', 'bookings.create', 'bookings.show', 'bookings.edit') ? 'bg-pink-50 text-pink-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Booking
            </a>

            <a href="{{ route('bookings.calendar') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('bookings.calendar') ? 'bg-pink-50 text-pink-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-2 9h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h2m4-6v6m-3-3h6" />
                </svg>
                Kalender Booking
            </a>

            <a href="{{ route('clients.index') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('clients.*') ? 'bg-pink-50 text-pink-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Klien
            </a>

            <a href="{{ route('services.index') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('services.*') ? 'bg-pink-50 text-pink-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Layanan
            </a>

            <a href="{{ route('invoices.index') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('invoices.*') ? 'bg-pink-50 text-pink-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Invoice
            </a>

            @if (auth()->user()->isAdmin())
                <div class="pt-3 mt-3 border-t border-gray-100">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Admin</p>
                    <a href="{{ route('admin.users') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.*') ? 'bg-pink-50 text-pink-600' : 'text-gray-600 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Kelola Pengguna
                    </a>
                </div>
            @endif
        </nav>

        {{-- Bottom: profile & logout --}}
        <div class="border-t border-gray-200 px-3 py-4 space-y-1">
            <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profil
            </a>
            <button wire:click="logout" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 w-full text-left">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Keluar
            </button>
        </div>
    </div>

    {{-- Overlay for mobile --}}
    <div x-show="mobileOpen" @click="mobileOpen = false" class="fixed inset-0 z-30 bg-black bg-opacity-50 sm:hidden">
    </div>
</aside>
