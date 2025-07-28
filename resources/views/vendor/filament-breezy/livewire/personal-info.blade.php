<x-filament::section>
    <x-slot name="heading">
        Informations personnelles
    </x-slot>
    <x-slot name="description">
        Gérer vos informations personnelles et professionnelles.
    </x-slot>

    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" color="primary">
                Mettre à jour
            </x-filament::button>
        </div>
    </form>
</x-filament::section>
