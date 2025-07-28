<x-filament-panels::page class="fi-my-profile-page">
    <div class="space-y-6">
        @foreach($this->getVisibleComponents() as $component)
            @php $componentId = $component->getName(); @endphp
            
            @if($componentId === 'personal_info')
                @livewire(\App\Filament\Breezy\MyProfileComponents\PersonalInfoComponent::class)
            @elseif($componentId === 'update_password')
                @livewire(\App\Filament\Breezy\MyProfileComponents\UpdatePasswordComponent::class)
            @elseif($componentId === 'two_factor')
                <x-filament::section>
                    <x-slot name="heading">
                        Authentification à deux facteurs
                    </x-slot>
                    <x-slot name="description">
                        Gérez l'authentification à deux facteurs pour votre compte (recommandé).
                    </x-slot>
                    
                    @livewire(\Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication::class)
                </x-filament::section>
            @elseif($componentId === 'sanctum_tokens')
                <x-filament::section>
                    <x-slot name="heading">
                        Jetons d'API
                    </x-slot>
                    <x-slot name="description">
                        Gérez les jetons d'API qui permettent aux services tiers d'accéder à cette application en votre nom.
                    </x-slot>
                    
                    @livewire(\Jeffgreco13\FilamentBreezy\Livewire\SanctumTokens::class)
                </x-filament::section>
            @else
                @livewire($component::class)
            @endif
        @endforeach
    </div>
</x-filament-panels::page>