<?php

namespace App\Filament\Breezy\MyProfileComponents;

use App\Settings\SecuritySettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class UpdatePasswordComponent extends MyProfileComponent
{
    protected string $view = "filament-breezy::livewire.update-password";

    public array $only = ['current_password', 'password', 'password_confirmation'];

    public array $data = [];
    
    public $user;

    public function mount()
    {
        $this->user = \Filament\Facades\Filament::getCurrentPanel()->auth()->user();
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        $securitySettings = app(SecuritySettings::class);
        
        // Construire les règles de validation basées sur les settings
        $passwordRules = Password::min($securitySettings->password_min_length);
        
        if ($securitySettings->password_require_uppercase) {
            $passwordRules->mixedCase();
        }
        
        if ($securitySettings->password_require_numbers) {
            $passwordRules->numbers();
        }
        
        if ($securitySettings->password_require_symbols) {
            $passwordRules->symbols();
        }

        return [
            Section::make('Changer le mot de passe')
                ->description('Assurez-vous d\'utiliser un mot de passe long et aléatoire pour rester en sécurité.')
                ->schema([
                    TextInput::make('current_password')
                        ->label('Mot de passe actuel')
                        ->password()
                        ->required()
                        ->currentPassword()
                        ->columnSpanFull(),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('password')
                                ->label('Nouveau mot de passe')
                                ->password()
                                ->required()
                                ->rule($passwordRules)
                                ->confirmed()
                                ->helperText($this->getPasswordRequirements($securitySettings)),

                            TextInput::make('password_confirmation')
                                ->label('Confirmer le mot de passe')
                                ->password()
                                ->required()
                                ->dehydrated(false),
                        ]),
                ]),
        ];
    }

    private function getPasswordRequirements(SecuritySettings $settings): string
    {
        $requirements = [];
        $requirements[] = "Au moins {$settings->password_min_length} caractères";
        
        if ($settings->password_require_uppercase) {
            $requirements[] = "une majuscule";
        }
        
        if ($settings->password_require_lowercase) {
            $requirements[] = "une minuscule";
        }
        
        if ($settings->password_require_numbers) {
            $requirements[] = "un chiffre";
        }
        
        if ($settings->password_require_symbols) {
            $requirements[] = "un caractère spécial";
        }

        return 'Requis: ' . implode(', ', $requirements);
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        
        $this->user->update([
            'password' => Hash::make($data['password'])
        ]);

        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_' . auth()->getDefaultDriver() => $this->user->getAuthPassword(),
            ]);
        }

        $this->form->fill();
        $this->notify('success', 'Mot de passe mis à jour avec succès.');
    }
}