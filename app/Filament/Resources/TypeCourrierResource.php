<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeCourrierResource\Pages;
use App\Filament\Resources\TypeCourrierResource\RelationManagers;
use App\Models\TypeCourrier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeCourrierResource extends Resource
{
    protected static ?string $model = TypeCourrier::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationLabel = 'Types de courriers';
    protected static ?string $modelLabel = 'Type de courrier';
    protected static ?string $pluralModelLabel = 'Types de courriers';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 1;

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
                                    ->helperText('Code court pour identifier le type'),
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
                                        'heroicon-o-envelope' => 'Enveloppe',
                                        'heroicon-o-envelope-open' => 'Enveloppe ouverte',
                                        'heroicon-o-document' => 'Document',
                                        'heroicon-o-document-text' => 'Document texte',
                                        'heroicon-o-clipboard-document' => 'Clipboard',
                                        'heroicon-o-paper-airplane' => 'Avion papier',
                                        'heroicon-o-inbox' => 'Boîte de réception',
                                        'heroicon-o-archive-box' => 'Archive',
                                    ])
                                    ->default('heroicon-o-envelope'),

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
                                Forms\Components\TextInput::make('delai_traitement_defaut')
                                    ->label('Délai de traitement par défaut (jours)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(7),

                                Forms\Components\Toggle::make('actif')
                                    ->label('Actif')
                                    ->default(true)
                                    ->helperText('Types inactifs cachés lors de la création de courriers'),
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
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\ColorColumn::make('couleur')
                    ->label('Couleur'),

                Tables\Columns\IconColumn::make('icone')
                    ->label('Icône'),

                Tables\Columns\TextColumn::make('delai_traitement_defaut')
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
            'index' => Pages\ListTypeCourriers::route('/'),
            'create' => Pages\CreateTypeCourrier::route('/create'),
            'edit' => Pages\EditTypeCourrier::route('/{record}/edit'),
        ];
    }
}
