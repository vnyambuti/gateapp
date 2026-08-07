<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="mt-10">
            <x-filament::button type="submit" color="warning">
                Gate Out
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
