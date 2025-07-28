<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeDocumentResource\Pages;
use App\Filament\Resources\TypeDocumentResource\RelationManagers;
use App\Models\TypeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeDocumentResource extends Resource
{
    protected static ?string $model = TypeDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Types de documents';
    protected static ?string $modelLabel = 'Type de document';
    protected static ?string $pluralModelLabel = 'Types de documents';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 2;

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
                                    ->default('#8B5CF6'),

                                Forms\Components\Select::make('icone')
                                    ->label('Icône')
                                    ->required()
                                    ->options([
                                        'heroicon-o-document' => 'Document',
                                        'heroicon-o-document-text' => 'Document texte',
                                        'heroicon-o-clipboard-document' => 'Clipboard',
                                        'heroicon-o-clipboard-document-list' => 'Liste documents',
                                        'heroicon-o-folder' => 'Dossier',
                                        'heroicon-o-folder-open' => 'Dossier ouvert',
                                        'heroicon-o-presentation-chart-line' => 'Rapport',
                                        'heroicon-o-scale' => 'Décision/Jugement',
                                        'heroicon-o-document-chart-bar' => 'Statistiques',
                                    ])
                                    ->default('heroicon-o-document'),

                                Forms\Components\TextInput::make('ordre_affichage')
                                    ->label('Ordre d\'affichage')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Plus petit = plus haut dans la liste'),
                            ]),
                    ]),

                Forms\Components\Section::make('Restrictions de fichiers')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TagsInput::make('extensions_autorisees')
                                    ->label('Extensions autorisées')
                                    ->placeholder('pdf')
                                    ->helperText('Extensions de fichiers acceptées (ex: pdf, doc, docx). Laisser vide pour accepter tout.')
                                    ->suggestions(['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'png']),

                                Forms\Components\Toggle::make('actif')
                                    ->label('Actif')
                                    ->default(true)
                                    ->helperText('Types inactifs cachés lors de l\'upload de documents'),
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

                Tables\Columns\TextColumn::make('extensions_texte')
                    ->label('Extensions')
                    ->getStateUsing(fn ($record) => $record->extensions_texte)
                    ->badge()
                    ->separator(','),

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
            'index' => Pages\ListTypeDocuments::route('/'),
            'create' => Pages\CreateTypeDocument::route('/create'),
            'edit' => Pages\EditTypeDocument::route('/{record}/edit'),
        ];
    }
}
