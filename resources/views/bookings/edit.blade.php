<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('bookings.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600">Booking</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-800 font-semibold">Edit Booking</span>
        </div>
    </x-slot>
    <livewire:bookings.booking-edit :booking="$booking" />
</x-app-layout>
