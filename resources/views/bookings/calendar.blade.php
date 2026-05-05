<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-3 w-full min-w-0">
            <h2 class="text-xl font-bold text-gray-800 truncate">Kalender Booking</h2>
            <a href="{{ route('bookings.index') }}" wire:navigate class="self-start sm:self-auto text-xs sm:text-sm text-pink-600 hover:text-pink-700 font-medium whitespace-nowrap">
                Lihat Daftar Booking
            </a>
        </div>
    </x-slot>

    <livewire:bookings.booking-calendar />
</x-app-layout>
