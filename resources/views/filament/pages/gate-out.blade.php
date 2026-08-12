<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div style="margin-top: 2.5rem;">
            <x-filament::button type="submit" color="warning">
                Gate Out
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
