<?php

namespace App\Filament\Pages\Settings;

use App\Settings\BreezySettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class BreezySettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Configuration profil';
    protected static ?string $title = 'Configuration des profils utilisateur';
    protected static ?string $navigationGroup = 'Paramètres';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.settings.breezy-settings';
    
    public $data = [];
    
    public function mount()
    {
        $settings = app(BreezySettings::class);
        $this->data = [
            'enable_registration' => $settings->enable_registration,
            'enable_password_reset' => $settings->enable_password_reset,
            'enable_profile_page' => $settings->enable_profile_page,
            'force_email_verification' => $settings->force_email_verification,
            'sanctum_abilities' => $settings->sanctum_abilities,
        ];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->color('primary')
                ->action('save'),
        ];
    }
    
    public function save()
    {
        $settings = app(BreezySettings::class);
        
        $settings->enable_registration = $this->data['enable_registration'];
        $settings->enable_password_reset = $this->data['enable_password_reset'];
        $settings->enable_profile_page = $this->data['enable_profile_page'];
        $settings->force_email_verification = $this->data['force_email_verification'];
        $settings->sanctum_abilities = $this->data['sanctum_abilities'];
        
        $settings->save();
        
        Notification::make()
            ->title('Configuration sauvegardée')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuration générale')
                    ->schema([
                        Forms\Components\Toggle::make('enable_registration')
                            ->label('Autoriser l\'inscription')
                            ->helperText('Permettre aux nouveaux utilisateurs de s\'inscrire')
                            ->live(),

                        Forms\Components\Toggle::make('enable_password_reset')
                            ->label('Autoriser la réinitialisation de mot de passe')
                            ->helperText('Permettre aux utilisateurs de réinitialiser leur mot de passe'),

                        Forms\Components\Toggle::make('enable_profile_page')
                            ->label('Activer la page de profil')
                            ->helperText('Permettre aux utilisateurs de modifier leur profil'),

                        Forms\Components\Toggle::make('force_email_verification')
                            ->label('Forcer la vérification d\'email')
                            ->helperText('Obliger les utilisateurs à vérifier leur email'),
                    ])->columns(2),

                Forms\Components\Section::make('Permissions API (Sanctum)')
                    ->schema([
                        Forms\Components\TagsInput::make('sanctum_abilities')
                            ->label('Capacités Sanctum')
                            ->helperText('Permissions par défaut pour les tokens API')
                            ->default(['*'])
                            ->placeholder('Entrez les capacités'),
                    ]),
            ])
            ->statePath('data');
    }
}