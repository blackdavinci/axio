<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrioriteResource\Pages;
use App\Filament\Resources\PrioriteResource\RelationManagers;
use App\Models\Priorite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrioriteResource extends Resource
{
    protected static ?string $model = Priorite::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Priorités';
    protected static ?string $modelLabel = 'Priorité';
    protected static ?string $pluralModelLabel = 'Priorités';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nom')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('code')
                                    ->label('Code')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Code court pour identifier la priorité'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Paramètres visuels')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\ColorPicker::make('couleur')
                                    ->label('Couleur')
                                    ->required()
                                    ->default('#3B82F6'),

                                Forms\Components\Select::make('icone')
                                    ->label('Icône')
                                    ->required()
                                    ->options([
                                        'heroicon-o-flag' => 'Drapeau',
                                        'heroicon-o-exclamation-triangle' => 'Triangle d\'alerte',
                                        'heroicon-o-bolt' => 'Éclair',
                                        'heroicon-o-fire' => 'Feu',
                                        'heroicon-o-clock' => 'Horloge',
                                        'heroicon-o-arrow-up' => 'Flèche haute',
                                        'heroicon-o-arrow-down' => 'Flèche basse',
                                        'heroicon-o-star' => 'Étoile',
                                    ])
                                    ->default('heroicon-o-flag'),

                                Forms\Components\TextInput::make('ordre_affichage')
                                    ->label('Ordre d\'affichage')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Plus petit = plus haut dans la liste'),
                            ]),
                    ]),

                Forms\Components\Section::make('Paramètres de traitement')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('delai_defaut')
                                    ->label('Délai par défaut (jours)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(7)
                                    ->helperText('Délai standard pour cette priorité'),

                                Forms\Components\Toggle::make('actif')
                                    ->label('Actif')
                                    ->default(true)
                                    ->helperText('Priorités inactives cachées lors de la sélection'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ordre_affichage')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color(fn ($record) => $record->couleur_badge)
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\ColorColumn::make('couleur')
                    ->label('Couleur'),

                Tables\Columns\IconColumn::make('icone')
                    ->label('Icône'),

                Tables\Columns\TextColumn::make('delai_defaut')
                    ->label('Délai (jours)')
                    ->suffix(' j')
                    ->sortable(),

                Tables\Columns\IconColumn::make('actif')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('actif')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Actifs seulement')
                    ->falseLabel('Inactifs seulement')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('ordre_affichage');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriorites::route('/'),
            'create' => Pages\CreatePriorite::route('/create'),
            'edit' => Pages\EditPriorite::route('/{record}/edit'),
        ];
    }
}
