<x-filament-panels::page>
    <div class="grid gap-6">
        @foreach ($this->getRegisteredMyProfileComponents() as $component)
            @unless(is_null($component))
                @livewire($component)
            @endunless
        @endforeach

        @if(class_exists(\Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication::class))
            <x-filament::section>
                <x-slot name="heading">
                    Authentification à deux facteurs
                </x-slot>
                <x-slot name="description">
                    Gérez l'authentification à deux facteurs pour votre compte (recommandé).
                </x-slot>

                @livewire(\Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication::class)
            </x-filament::section>
        @endif

        @if(class_exists(\Jeffgreco13\FilamentBreezy\Livewire\SanctumTokens::class))
            <x-filament::section>
                <x-slot name="heading">
                    Jetons d'API
                </x-slot>
                <x-slot name="description">
                    Gérez les jetons d'API qui permettent aux services tiers d'accéder à cette application en votre nom.
                </x-slot>

                @livewire(\Jeffgreco13\FilamentBreezy\Livewire\SanctumTokens::class)
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
