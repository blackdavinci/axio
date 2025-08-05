<?php

namespace App\Filament\Pages\Settings;

use App\Settings\SecuritySettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class SecuritySettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Sécurité';
    protected static ?string $title = 'Configuration de la sécurité';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 4;

    protected static string $settings = SecuritySettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Politique des mots de passe')
                    ->schema([
                        Forms\Components\TextInput::make('password_min_length')
                            ->label('Longueur minimale')
                            ->numeric()
                            ->minValue(6)
                            ->maxValue(128)
                            ->suffix('caractères')
                            ->required(),

                        Forms\Components\Toggle::make('password_require_uppercase')
                            ->label('Exiger des majuscules')
                            ->helperText('Au moins une lettre majuscule'),

                        Forms\Components\Toggle::make('password_require_lowercase')
                            ->label('Exiger des minuscules')
                            ->helperText('Au moins une lettre minuscule'),

                        Forms\Components\Toggle::make('password_require_numbers')
                            ->label('Exiger des chiffres')
                            ->helperText('Au moins un chiffre'),

                        Forms\Components\Toggle::make('password_require_symbols')
                            ->label('Exiger des symboles')
                            ->helperText('Au moins un caractère spécial'),

                        Forms\Components\TextInput::make('password_expiry_days')
                            ->label('Expiration des mots de passe')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365)
                            ->suffix('jours')
                            ->helperText('0 = pas d\'expiration'),
                    ])->columns(2),

                Forms\Components\Section::make('Sessions et connexions')
                    ->schema([
                        Forms\Components\TextInput::make('session_lifetime')
                            ->label('Durée de session')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(1440)
                            ->suffix('minutes')
                            ->required(),

                        Forms\Components\TextInput::make('max_login_attempts')
                            ->label('Tentatives de connexion maximum')
                            ->numeric()
                            ->minValue(3)
                            ->maxValue(20)
                            ->required(),

                        Forms\Components\TextInput::make('lockout_duration')
                            ->label('Durée de verrouillage')
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(1440)
                            ->suffix('minutes')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Sécurité avancée')
                    ->schema([
                        Forms\Components\Toggle::make('force_password_reset')
                            ->label('Forcer la réinitialisation du mot de passe')
                            ->helperText('Obliger tous les utilisateurs à changer leur mot de passe à la prochaine connexion'),

                        Forms\Components\Toggle::make('enable_2fa')
                            ->label('Activer l\'authentification à deux facteurs')
                            ->helperText('Permettre aux utilisateurs d\'activer la 2FA'),
                    ])->columns(2),
            ]);
    }
}
