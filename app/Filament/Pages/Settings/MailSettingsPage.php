<?php

namespace App\Filament\Pages\Settings;

use App\Settings\MailSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class MailSettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Email';
    protected static ?string $title = 'Configuration Email';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 2;

    protected static string $settings = MailSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Paramètres d\'envoi')
                    ->schema([
                        Forms\Components\TextInput::make('from_name')
                            ->label('Nom de l\'expéditeur')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('from_address')
                            ->label('Adresse email de l\'expéditeur')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('signature')
                            ->label('Signature par défaut')
                            ->rows(4)
                            ->maxLength(500)
                            ->placeholder('Sera ajoutée automatiquement aux emails'),
                    ])->columns(2),

                Forms\Components\Section::make('Notifications')
                    ->schema([
                        Forms\Components\Toggle::make('enable_notifications')
                            ->label('Activer les notifications')
                            ->live(),

                        Forms\Components\CheckboxList::make('notification_types')
                            ->label('Types de notifications')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'in_app' => 'Dans l\'application',
                                'push' => 'Notifications push',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('enable_notifications'))
                            ->columns(2),

                        Forms\Components\TextInput::make('notification_frequency')
                            ->label('Fréquence des notifications (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1440)
                            ->suffix('minutes')
                            ->visible(fn (Forms\Get $get) => $get('enable_notifications')),
                    ])->columns(2),

                Forms\Components\Section::make('Templates d\'emails')
                    ->schema([
                        Forms\Components\RichEditor::make('welcome_template')
                            ->label('Template d\'email de bienvenue')
                            ->placeholder('Template pour les nouveaux utilisateurs')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('reset_password_template')
                            ->label('Template de réinitialisation de mot de passe')
                            ->placeholder('Template pour la réinitialisation des mots de passe')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
