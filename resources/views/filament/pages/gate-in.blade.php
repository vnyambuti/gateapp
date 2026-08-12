<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div style="margin-top: 2.5rem;">

            <x-filament::button type="submit">
                Gate In
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
