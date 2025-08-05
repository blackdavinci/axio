<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StructureResource\Pages;
use App\Filament\Resources\StructureResource\RelationManagers;
use App\Models\Structure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StructureResource extends Resource
{
    protected static ?string $model = Structure::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'Structure organisationnelle';
    
    protected static ?string $modelLabel = 'Structure';
    
    protected static ?string $pluralModelLabel = 'Structures';
    
    protected static ?string $navigationGroup = 'Structure organisationnelle';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'departement' => 'Département',
                                'service' => 'Service',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('parent_id', null)),

                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),

                        Forms\Components\Select::make('parent_id')
                            ->label('Structure parente')
                            ->options(function (Forms\Get $get) {
                                $type = $get('type');
                                if (!$type) return [];

                                return match ($type) {
                                    'departement' => [], // Les départements n'ont pas de parent
                                    'service' => Structure::query()
                                        ->where(function ($query) {
                                            $query->where('type', 'departement')
                                                  ->orWhere(function ($q) {
                                                      $q->where('type', 'service');
                                                  });
                                        })
                                        ->actifs()
                                        ->orderBy('type')
                                        ->orderBy('nom')
                                        ->pluck('nom', 'id')
                                        ->toArray(),
                                };
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Rattaché à la Direction générale')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'service'),

                        Forms\Components\Select::make('chef_id')
                            ->label(fn (Forms\Get $get) => match ($get('type')) {
                                'departement' => 'Chef de département',
                                'service' => 'Chef de service',
                                default => 'Responsable',
                            })
                            ->relationship('chef', 'nom')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->fullName())
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('actif')
                            ->default(true),

                        Forms\Components\TextInput::make('ordre')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                    ])
                    ->collapsible(),

                // Champ code caché (généré automatiquement)
                Forms\Components\Hidden::make('code'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $indent = str_repeat('→ ', $record->parent_id ? 1 : 0);
                        return $indent . $record->nom;
                    }),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->getStateUsing(fn ($record) => $record->type_label)
                    ->colors([
                        'primary' => 'departement',
                        'success' => 'service',
                    ]),

                Tables\Columns\TextColumn::make('parent.nom')
                    ->label('Structure parente')
                    ->placeholder('Direction générale')
                    ->sortable(),

                Tables\Columns\TextColumn::make('chef.nom')
                    ->label('Responsable')
                    ->getStateUsing(fn ($record) => $record->chef?->fullName())
                    ->placeholder('Non assigné'),

                Tables\Columns\TextColumn::make('enfants_count')
                    ->label('Sous-structures')
                    ->counts('enfants')
                    ->sortable(),

                Tables\Columns\TextColumn::make('utilisateurs_count')
                    ->label('Utilisateurs')
                    ->counts('utilisateurs')
                    ->sortable(),

                Tables\Columns\IconColumn::make('actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'departement' => 'Département',
                        'service' => 'Service',
                    ]),

                Tables\Filters\TernaryFilter::make('actif'),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Structure parente')
                    ->relationship('parent', 'nom'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth('5xl'),
                    
                Tables\Actions\EditAction::make()
                    ->modalWidth('4xl'),
                    
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth('4xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Le code sera généré automatiquement par le modèle
                        return $data;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('type')
            ->defaultSort('ordre')
            ->poll('30s');
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
            'index' => Pages\ListStructures::route('/'),
            // Pas de pages create/edit - tout se fait en modal
        ];
    }
}
