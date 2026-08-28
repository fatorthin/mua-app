<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Edit Dokumen Pricelist</h2>
    </x-slot>

    <livewire:pricelists.pricelist-builder :pricelist="$pricelist" />
</x-app-layout>
