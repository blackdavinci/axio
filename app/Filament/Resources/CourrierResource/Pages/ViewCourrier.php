<?php

namespace App\Filament\Resources\CourrierResource\Pages;

use App\Filament\Resources\CourrierResource;
use App\Models\Priorite;
use App\Models\Structure;
use App\Models\User;
use App\Notifications\CourrierAssignedNotification;
use Coolsam\NestedComments\Filament\Actions\CommentsAction;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\Model;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;

class ViewCourrier extends ViewRecord
{
    protected static string $resource = CourrierResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Courrier')
                    ->tabs([
                        Tabs\Tab::make('Détails')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(2)->schema([
                                    Section::make('Informations générales')
                                        ->schema([
                                            TextEntry::make('numero_courrier')
                                                ->label('Numéro de courrier')
                                                ->badge()
                                                ->color('primary'),

                                            TextEntry::make('reference')
                                                ->label('Référence')
                                                ->badge()
                                                ->color('gray')
                                                ->visible(fn ($record) => !empty($record->reference)),

                                            TextEntry::make('objet')
                                                ->label('Objet')
                                                ->columnSpanFull()
                                                ->weight('bold'),

                                            TextEntry::make('statut')
                                                ->label('Statut')
                                                ->badge()
                                                ->getStateUsing(fn ($record) => $record->statut_label)
                                                ->color(fn ($record) => $record->statut_color),

                                            TextEntry::make('typeCourrier.nom')
                                                ->label('Type de courrier')
                                                ->badge()
                                                ->color(fn ($record) => $record->typeCourrier?->couleur ?? 'gray'),

                                            TextEntry::make('priorite.nom')
                                                ->label('Priorité')
                                                ->badge()
                                                ->color(fn ($record) => $record->priorite?->couleur_badge ?? 'gray'),

                                            TextEntry::make('date_reception')
                                                ->label('Date de réception')
                                                ->date('d/m/Y'),

                                            TextEntry::make('date_limite_traitement')
                                                ->label('Date limite de traitement')
                                                ->dateTime('d/m/Y H:i')
                                                ->color(fn ($record) => $record->date_limite_traitement && $record->date_limite_traitement->isPast() ? 'danger' : null),

                                            TextEntry::make('created_at')
                                                ->label('Créé le')
                                                ->dateTime('d/m/Y H:i'),
                                        ])
                                        ->collapsible()
                                        ->columns(3)
                                        ->columnSpan(2),

                                    Section::make('Informations expéditeur')
                                        ->schema([
                                            TextEntry::make('expediteur.nom')
                                                ->label('Nom/Dénomination')
                                                ->size('lg')
                                                ->weight('bold')
                                                ->visible(fn ($record) => $record->expediteur_id),

                                            TextEntry::make('expediteur.type')
                                                ->label('Type d\'expéditeur')
                                                ->badge()
                                                ->formatStateUsing(fn (string $state): string => match($state) {
                                                    'personne' => 'Personne physique',
                                                    'entreprise' => 'Entreprise',
                                                    'administration' => 'Administration',
                                                    default => $state,
                                                })
                                                ->color(fn (string $state): string => match($state) {
                                                    'personne' => 'primary',
                                                    'entreprise' => 'success',
                                                    'administration' => 'warning',
                                                    default => 'gray',
                                                })
                                                ->visible(fn ($record) => $record->expediteur_id),

                                            TextEntry::make('expediteur.telephone')
                                                ->label('Téléphone')
                                                ->icon('heroicon-o-phone')
                                                ->visible(fn ($record) => $record->expediteur_id && !empty($record->expediteur->telephone)),

                                            TextEntry::make('expediteur.email')
                                                ->label('Adresse e-mail')
                                                ->icon('heroicon-o-envelope')
                                                ->visible(fn ($record) => $record->expediteur_id && !empty($record->expediteur->email)),

                                            TextEntry::make('expediteur.adresse')
                                                ->label('Adresse postale')
                                                ->icon('heroicon-o-map-pin')
                                                ->columnSpanFull()
                                                ->visible(fn ($record) => $record->expediteur_id && !empty($record->expediteur->adresse)),
                                        ])
                                        ->collapsible()
                                        ->columns(2)
                                        ->columnSpan(2)
                                        ->visible(fn ($record) => $record->expediteur_id),
                                ]),

                                // PDF Viewer pour le premier document PDF
                                Section::make('Courrier')
                                    ->icon('heroicon-o-document-magnifying-glass')
                                    ->schema([
                                        PdfViewerEntry::make('pdf_preview')
                                            ->label('')
                                            ->fileUrl(function ($record) {
                                                $pdfMedia = $record->media()
                                                    ->where('mime_type', 'application/pdf')
                                                    ->first();
                                                return $pdfMedia ? url('media/' . $pdfMedia->id . '/show') : null;
                                            })
                                            ->minHeight('800px')
                                            ->columnSpanFull(),
                                    ])->collapsible()
                                    ->compact()


                                    ->visible(function ($record) {
                                        return $record->media()
                                            ->where('mime_type', 'application/pdf')
                                            ->exists();
                                    }),

                                Section::make('Assignation et traitement')
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Assigné à')
                                            ->badge()
                                            ->color('success')
                                            ->icon('heroicon-o-user')
                                            ->visible(fn ($record) => $record->user_id),

                                        TextEntry::make('createdBy.name')
                                            ->label('Créé par')
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-o-user-plus'),

                                        TextEntry::make('service.nom')
                                            ->label('Service')
                                            ->badge()
                                            ->color('gray')
                                            ->icon('heroicon-o-building-office')
                                            ->visible(fn ($record) => $record->service_id),
                                    ])->columns(3),


                            ]),

                        Tabs\Tab::make('Documents')
                            ->icon('heroicon-o-document-duplicate')
                            ->badge(fn ($record) => $record->media->count())
                            ->schema([
                                Section::make('Documents joints')
                                    ->description('Tous les documents et fichiers associés à ce courrier')
                                    ->schema([
                                        ViewEntry::make('documents_list')
                                            ->label('')
                                            ->view('filament.resources.courrier-resource.pages.components.documents-list')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Assignations')
                            ->icon('heroicon-o-users')
                            ->badge(fn ($record) => $record->assignments->count())
                            ->schema([
                                Section::make('Historique des assignations')
                                    ->description('Toutes les assignations de ce courrier')
                                    ->schema([
                                        RepeatableEntry::make('assignments')
                                            ->label('')
                                            ->schema([
                                                Grid::make(3)->schema([
                                                    TextEntry::make('structure.nom')
                                                        ->label('Structure assignée')
                                                        ->badge()
                                                        ->color('primary')
                                                        ->icon('heroicon-o-building-office'),
                                                    
                                                    TextEntry::make('user.name')
                                                        ->label('Agent assigné')
                                                        ->badge()
                                                        ->color('success')
                                                        ->icon('heroicon-o-user')
                                                        ->visible(fn ($record) => $record->user_id),
                                                    
                                                    TextEntry::make('assignedBy.name')
                                                        ->label('Assigné par')
                                                        ->badge()
                                                        ->color('info')
                                                        ->icon('heroicon-o-user-plus'),
                                                ]),
                                                
                                                TextEntry::make('assigned_at')
                                                    ->label('Date d\'assignation')
                                                    ->dateTime('d/m/Y à H:i')
                                                    ->since()
                                                    ->icon('heroicon-o-calendar'),
                                                
                                                TextEntry::make('notes')
                                                    ->label('Notes')
                                                    ->markdown()
                                                    ->visible(fn ($record) => !empty($record->notes))
                                                    ->columnSpanFull(),
                                                
                                                // Affichage des pièces jointes de l'assignation
                                                RepeatableEntry::make('media')
                                                    ->label('Pièces jointes à cette assignation')
                                                    ->schema([
                                                        TextEntry::make('name')
                                                            ->label('Fichier')
                                                            ->url(fn ($state, $record) => $record->getUrl())
                                                            ->openUrlInNewTab()
                                                            ->icon('heroicon-o-paper-clip'),
                                                        TextEntry::make('human_readable_size')
                                                            ->label('Taille')
                                                            ->badge()
                                                            ->color('gray'),
                                                    ])
                                                    ->columns(2)
                                                    ->visible(fn ($record) => $record->media->count() > 0),
                                            ])
                                            ->contained()
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn ($record) => $record->assignments->count() > 0),
                                
                                Section::make('Aucune assignation')
                                    ->description('Ce courrier n\'a pas encore été assigné.')
                                    ->visible(fn ($record) => $record->assignments->count() === 0),
                            ]),

                        Tabs\Tab::make('Historique')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Historique des modifications')
                                    ->description('Suivi complet du cheminement du courrier depuis sa création')
                                    ->schema([
                                        ViewEntry::make('activities_timeline')
                                            ->label('')
                                            ->view('filament.resources.courrier-resource.pages.components.courrier-activities-simple')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Action d'assignation pour la page ViewCourrier
            Actions\Action::make('assign_courrier')
                ->label('Assigner Courrier')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->visible(fn (\App\Models\Courrier $record): bool =>
                    !in_array($record->statut, ['traite', 'archive', 'rejete']) &&
                    auth()->user()->can('assign_courrier')
                )
                ->form(function (\App\Models\Courrier $record) {
                    return [
                        Select::make('structure_id')
                            ->label('Assigner à la Structure')
                            ->options(Structure::whereIn('type', ['departement', 'service'])->pluck('nom', 'id'))
                            ->default($record->currentAssignment?->structure_id)
                            ->required()
                            ->live(),
                        Select::make('user_id')
                            ->label('Assigner à l\'Agent')
                            ->options(fn (Get $get) =>
                            User::where('structure_id', $get('structure_id'))
                                ->pluck('name', 'id')
                            )
                            ->default($record->currentAssignment?->user_id)
                            ->hint('Sélectionnez un agent de la structure choisie.')
                            ->nullable(),
                        Select::make('priorite_id')
                            ->label('Priorité de Traitement')
                            ->options(Priorite::all()->pluck('nom', 'id'))
                            ->default($record->priorite_id)
                            ->required(),
                        DateTimePicker::make('date_limite')
                            ->label('Date Limite de Traitement')
                            ->default($record->date_limite)
                            ->minDate(now())
                            ->required(),
                        Textarea::make('notes')
                            ->label('Notes d\'Assignation')
                            ->default($record->currentAssignment?->notes)
                            ->maxLength(65535)
                            ->rows(5)
                            ->nullable(),
                        SpatieMediaLibraryFileUpload::make('assignment_attachments')
                            ->label('Pièces jointes à cette assignation')
                            ->collection('assignment_attachments')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->visibility('private')
                            ->nullable(),
                    ];
                })
                ->action(function (array $data, \App\Models\Courrier $record): void {
                    $record->update([
                        'priorite_id' => $data['priorite_id'],
                        'date_limite' => $data['date_limite'],
                        'statut' => 'affecte',
                    ]);

                    $assignment = $record->assignments()->create([
                        'structure_id' => $data['structure_id'],
                        'user_id' => $data['user_id'],
                        'notes' => $data['notes'],
                        'assigned_by_user_id' => auth()->id(),
                        'assigned_at' => now(),
                    ]);

                    if (!empty($data['assignment_attachments'])) {
                        foreach ($data['assignment_attachments'] as $attachmentPath) {
                            $assignment->addMedia($attachmentPath)
                                ->toMediaCollection('assignment_attachments');
                        }
                    }

                    if ($data['user_id']) {
                        $assignedUser = User::find($data['user_id']);
                        if ($assignedUser) {
                            $assignedUser->notify(new CourrierAssignedNotification($record, $assignment));
                        }
                    }

                    $record->refresh();
                    $this->refreshInfolist();

                    Notification::make()
                        ->title('Courrier assigné avec succès !')
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel('Assigner'),

            CommentsAction::make()
                ->badgeColor('danger')
                ->color('info')
                ->badge(fn(Model $record) => $record->getAttribute('comments_count')),
            Actions\Action::make('add_attachments')
                ->label('Ajouter Fichiers')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn ($record) => !in_array($record->statut, ['archive', 'rejete']))
                ->form([
                    SpatieMediaLibraryFileUpload::make('new_attachments')
                        ->label('Sélectionner les fichiers à ajouter')
                        ->collection('attachments')
                        ->multiple()
                        ->maxFiles(5)
                        ->acceptedFileTypes([
                            'application/pdf', 
                            'image/*', 
                            'application/msword', 
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ])
                        ->disk('private')
                        ->visibility('private')
                        ->required()
                        ->columnSpanFull(),
                    
                    Textarea::make('attachment_comment')
                        ->label('Commentaire explicatif')
                        ->placeholder('Ajoutez un commentaire pour expliquer ces documents...')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data, \App\Models\Courrier $record): void {
                    // Enregistrer le commentaire s'il existe
                    if (!empty($data['attachment_comment'])) {
                        $record->addComment($data['attachment_comment'], auth()->user());
                    }

                    $this->refreshInfolist();

                    Notification::make()
                        ->title('Fichiers ajoutés avec succès !')
                        ->body('Les documents ont été joints au courrier.')
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel('Ajouter les fichiers')
                ->modalWidth('lg'),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
