<div class="grid gap-6">
    <x-filament::section>
        <x-slot name="heading">
            Changer le mot de passe
        </x-slot>
        <x-slot name="description">
            Assurez-vous d'utiliser un mot de passe long et aléatoire pour rester en sécurité.
        </x-slot>
        
        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}
            
            <div class="flex justify-end">
                <x-filament::button type="submit" color="primary">
                    Mettre à jour le mot de passe
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</div>