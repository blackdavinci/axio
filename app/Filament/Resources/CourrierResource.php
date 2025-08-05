<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourrierResource\Pages;
use App\Filament\Resources\CourrierResource\RelationManagers;
use App\Models\Courrier;
use App\Models\Expediteur;
use Coolsam\NestedComments\Filament\Tables\Actions\CommentsAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use VictorScatolon\FilamentAttachmentLibrary\Forms\Components\AttachmentLibraryFileUpload;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Filament\Infolists\Infolist;

class CourrierResource extends Resource
{
    protected static ?string $model = Courrier::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Courriers';

    protected static ?string $navigationGroup = 'Gestion documentaire';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du courrier')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('type_courrier_id')
                                ->label('Type de courrier')
                                ->relationship('typeCourrier', 'nom')
                                ->searchable()
                                ->required()
                                ->preload(),

                            Select::make('expediteur_id')
                                ->label('Expéditeur')
                                ->relationship('expediteur', 'nom')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('nom')
                                        ->label('Nom')
                                        ->required()
                                        ->maxLength(255),

                                    Select::make('type')
                                        ->label('Type')
                                        ->options([
                                            'personne' => 'Personne',
                                            'entreprise' => 'Entreprise',
                                            'administration' => 'Administration',
                                            'autre' => 'Autre'
                                        ])
                                        ->default('personne')
                                        ->required(),

                                    PhoneInput::make('telephone')
                                        ->label('Téléphone')
                                        ->defaultCountry('GN')
                                        ->validateFor(lenient: true)
                                        ->required(),

                                    TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->maxLength(255),

                                    Textarea::make('adresse')
                                        ->label('Adresse')
                                        ->rows(3),
                                ])
                                ->createOptionAction(
                                    fn (Action $action) => $action
                                        ->modalHeading('Créer un nouvel expéditeur')
                                        ->modalSubmitActionLabel('Créer')
                                        ->modalWidth('lg')
                                )

                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    if ($state) {
                                        $expediteur = Expediteur::find($state);
                                        if ($expediteur) {
                                            $set('expediteur_nom', $expediteur->nom);
                                            $set('expediteur_type', $expediteur->type);
                                            $set('expediteur_telephone', $expediteur->telephone);
                                            $set('expediteur_email', $expediteur->email);
                                            $set('expediteur_adresse', $expediteur->adresse);
                                        }
                                    }
                                })->required(),
                        ]),

                        TextInput::make('objet')
                            ->label('Objet')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('reference')
                            ->label('Référence du courrier'),

                        Select::make('priorite_id')
                            ->label('Priorité')
                            ->relationship('priorite', 'nom')
                            ->searchable()
                            ->preload(),

                        DatePicker::make('date_reception')
                            ->label('Date de Réception')
                            ->required()
                            ->default(now()),

                        SpatieMediaLibraryFileUpload::make('attachments')
                            ->label('Documents joints')
                            ->helperText('Joindre le courrier')
                            ->collection('attachments')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->downloadable()
                            ->visibility('private')
                            ->columnSpanFull(),

                        Textarea::make('commentaires')
                            ->label('Commentaires')
                            ->rows(3)
                            ->columnSpanFull(),

                    ])->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_courrier')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('objet')
                    ->label('Objet')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\BadgeColumn::make('statut')
                    ->label('Statut')
                    ->getStateUsing(fn ($record) => $record->statut_label)
                    ->colors([
                        'info' => 'recu',
                        'warning' => 'en_cours',
                        'success' => 'traite',
                        'gray' => 'archive',
                        'danger' => 'rejete',
                    ]),

                Tables\Columns\TextColumn::make('typeCourrier.nom')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($record) => $record->typeCourrier?->couleur ?? 'gray'),

                Tables\Columns\TextColumn::make('priorite.nom')
                    ->label('Priorité')
                    ->badge()
                    ->color(fn ($record) => $record->priorite?->couleur_badge ?? 'gray'),

                Tables\Columns\TextColumn::make('user.nom')
                    ->label('Assigné à')
                    ->getStateUsing(fn ($record) => $record->user?->fullName()),

                Tables\Columns\TextColumn::make('date_limite')
                    ->label('Échéance')
                    ->dateTime('d/m/Y H:i')
                    ->color(fn ($record) => $record->date_limite && $record->date_limite->isPast() ? 'danger' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'recu' => 'Reçu',
                        'en_cours' => 'En cours',
                        'traite' => 'Traité',
                        'archive' => 'Archivé',
                        'rejete' => 'Rejeté',
                    ]),

                Tables\Filters\SelectFilter::make('type_courrier_id')
                    ->label('Type')
                    ->relationship('typeCourrier', 'nom'),

                Tables\Filters\SelectFilter::make('priorite_id')
                    ->label('Priorité')
                    ->relationship('priorite', 'nom'),

                Tables\Filters\Filter::make('en_retard')
                    ->label('En retard')
                    ->query(fn (Builder $query) => $query->where('date_limite', '<', now())),
            ])
            ->actions([
                CommentsAction::make('Commentaires')
                    ->button()
                    ->badgeColor('danger')
                    ->color('info')
                    ->badge(fn(Courrier $record) => $record->getAttribute('comments_count')),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCourriers::route('/'),
            'create' => Pages\CreateCourrier::route('/create'),
            'view' => Pages\ViewCourrier::route('/{record}'),
            'edit' => Pages\EditCourrier::route('/{record}/edit'),
        ];
    }
}
