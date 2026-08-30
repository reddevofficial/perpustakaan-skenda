<x-filament-panels::page>
    <form wire:submit="generate">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Generate Laporan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
