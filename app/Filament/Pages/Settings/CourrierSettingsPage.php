<?php

namespace App\Filament\Pages\Settings;

use App\Settings\CourrierSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class CourrierSettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Configuration courrier';
    protected static ?string $title = 'Configuration du courrier';
    protected static ?string $navigationGroup = 'Paramètres';
    protected static ?int $navigationSort = 3;

    protected static string $settings = CourrierSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Numérotation automatique')
                    ->schema([
                        Forms\Components\TextInput::make('courrier_entrant_prefix')
                            ->label('Préfixe courrier entrant')
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('courrier_sortant_prefix')
                            ->label('Préfixe courrier sortant')
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('numero_format')
                            ->label('Format du numéro')
                            ->required()
                            ->placeholder('{prefix}-{year}-{counter:4}')
                            ->helperText('Variables disponibles: {prefix}, {year}, {month}, {counter:X}'),

                        Forms\Components\TextInput::make('courrier_entrant_counter')
                            ->label('Compteur courrier entrant')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\TextInput::make('courrier_sortant_counter')
                            ->label('Compteur courrier sortant')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Délais de traitement')
                    ->schema([
                        Forms\Components\TextInput::make('delai_traitement_standard')
                            ->label('Délai standard (jours)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->suffix('jours')
                            ->required(),

                        Forms\Components\TextInput::make('delai_traitement_urgent')
                            ->label('Délai urgent (jours)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->suffix('jours')
                            ->required(),

                        Forms\Components\TextInput::make('delai_escalade')
                            ->label('Délai d\'escalade automatique (jours)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->suffix('jours')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Paramètres de workflow')
                    ->schema([
                        Forms\Components\Toggle::make('auto_attribution')
                            ->label('Attribution automatique')
                            ->helperText('Attribuer automatiquement les courriers selon les règles définies'),
                    ]),

                Forms\Components\Section::make('Niveaux de priorité')
                    ->schema([
                        Forms\Components\Repeater::make('niveaux_priorite')
                            ->label('Configurer les niveaux de priorité')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Libellé')
                                    ->required(),

                                Forms\Components\Select::make('color')
                                    ->label('Couleur')
                                    ->options([
                                        'gray' => 'Gris',
                                        'blue' => 'Bleu',
                                        'green' => 'Vert',
                                        'yellow' => 'Jaune',
                                        'orange' => 'Orange',
                                        'red' => 'Rouge',
                                        'purple' => 'Violet',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('delai')
                                    ->label('Délai (jours)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(30)
                                    ->suffix('jours')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(4)
                            ->addActionLabel('Ajouter un niveau')
                            ->collapsible(),
                    ]),
            ]);
    }
}