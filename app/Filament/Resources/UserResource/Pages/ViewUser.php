<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Précharger les relations pour éviter N+1 queries
        $this->record->load([
            'service.parent',
            'roles',
            'permissions'
        ]);

        return $data;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section avec photo à gauche et infos à droite comme dans StudentResource
                Section::make()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 3,
                        ])->schema([
                            Grid::make(1)
                                ->schema([
                                    ImageEntry::make('photo')
                                        ->label('')
                                        ->circular()
                                        ->size(120)
                                        ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&color=7F9CF5&background=EBF4FF'),

                                    TextEntry::make('roles.name')
                                        ->label('Rôle')
                                        ->hiddenLabel()
                                        ->badge()
                                        ->color('success')
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            '1' => 'UTILISATEUR ACTIF',
                                            '0' => 'UTILISATEUR INACTIF',
                                            default => $state,
                                        })
                                        ->color(fn (string $state): string => match ($state) {
                                            '1' => 'success',
                                            '0' => 'warning',
                                            default => 'gray',
                                        })
                                        ->placeholder('Aucun rôle assigné'),

                                    TextEntry::make('actif')
                                        ->label('Statut d\'emploi')
                                        ->hiddenLabel()
                                        ->badge()

                                ])
                                ->columnSpan(1),
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('fullName')
                                        ->label('Nom complet')
                                        ->getStateUsing(fn ($record) => $record->fullName())
                                        ->size('lg')
                                        ->weight('bold'),

                                    TextEntry::make('matricule')
                                        ->label('Matricule')
                                        ->badge()
                                        ->icon('heroicon-m-identification')
                                        ->placeholder('Non renseigné'),

                                    TextEntry::make('service.nom')
                                        ->label('Service')
                                        ->badge()
                                        ->color('primary')
                                        ->icon('heroicon-m-building-office')
                                        ->placeholder('Aucun service assigné'),

                                    TextEntry::make('poste')
                                        ->label('Poste/Fonction')
                                        ->placeholder('Non renseigné'),

                                    TextEntry::make('telephone')
                                        ->label('Téléphone principal')
                                        ->icon('heroicon-m-phone')
                                        ->url(fn ($record) => $record->telephone ? "tel:{$record->telephone}" : null)
                                        ->placeholder('Non renseigné'),

                                    TextEntry::make('email')
                                        ->label('Email')
                                        ->copyable()
                                        ->icon('heroicon-m-envelope')
                                        ->url(fn ($record) => "mailto:{$record->email}"),


                                ])
                                ->columnSpan(2)
                        ]),


                    ]),

                Section::make('Informations personnelles')
                    ->schema([
                        TextEntry::make('genre')
                            ->label('Genre')
                            ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculin' : 'Féminin')
                            ->badge()
                            ->color(fn ($state) => $state === 'M' ? 'success' : 'danger'),

                        TextEntry::make('date_naissance')
                            ->label('Date de naissance')
                            ->date()
                            ->icon('heroicon-m-calendar')
                            ->placeholder('Non renseignée'),

                        TextEntry::make('person.birth_place')
                            ->label('Lieu de naissance')
                            ->placeholder('Non renseigné'),

                        TextEntry::make('categorie')
                            ->label('Catégorie')
                            ->formatStateUsing(fn ($state) => match($state) {
                                'fonctionnaire' => 'Fonctionnaire',
                                'contractuel' => 'Contractuel',
                                'consultant' => 'Consultant',
                                'stagiaire' => 'Stagiaire',
                                default => $state
                            })
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'fonctionnaire' => 'success',
                                'contractuel' => 'warning',
                                'consultant' => 'info',
                                'stagiaire' => 'gray',
                                default => 'gray'
                            }),


                        TextEntry::make('grade')
                            ->label('Grade')
                            ->icon('heroicon-m-star')
                            ->placeholder('Non renseigné'),

                        TextEntry::make('specialite')
                            ->label('Spécialité')
                            ->icon('heroicon-m-academic-cap')
                            ->placeholder('Non renseignée'),

                    ])->columns(3)->icon('heroicon-o-user'),

                Section::make('Contacts et Adresse ')
                    ->schema([

                        TextEntry::make('adresse')
                            ->label('Adresse')
                            ->icon('heroicon-m-map-pin')
                            ->placeholder('Non renseignée'),
                        TextEntry::make('telephone_secondaire')
                            ->label('Téléphone secondaire')
                            ->icon('heroicon-m-phone')
                            ->url(fn ($record) => $record->telephone_secondaire ? "tel:{$record->telephone_secondaire}" : null)
                            ->placeholder('Non renseigné'),

                        TextEntry::make('personne_urgence')
                            ->label('Contact d\'urgence')
                            ->icon('heroicon-m-exclamation-triangle')
                            ->placeholder('Non renseigné'),

                        TextEntry::make('telephone_urgence')
                            ->label('Téléphone urgence')
                            ->icon('heroicon-m-phone')
                            ->url(fn ($record) => $record->telephone_urgence ? "tel:{$record->telephone_urgence}" : null)
                            ->placeholder('Non renseigné'),




                    ])->columns(2)->icon('heroicon-o-information-circle'),


                Section::make('Informations système')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Compte créé le')
                            ->dateTime()
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Dernière modification')
                            ->dateTime()
                            ->since()
                            ->icon('heroicon-m-pencil'),

                        TextEntry::make('email_verified_at')
                            ->label('Email vérifié le')
                            ->dateTime()
                            ->placeholder('Email non vérifié')
                            ->icon('heroicon-m-shield-check'),
                    ])->columns(3)->icon('heroicon-o-chart-bar'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-m-pencil')
                ->color('primary'),

            Actions\Action::make('resetPassword')
                ->label('Réinitialiser le mot de passe')
                ->icon('heroicon-m-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Réinitialiser le mot de passe')
                ->modalDescription('Un email de réinitialisation sera envoyé à l\'utilisateur.')
                ->action(function ($record) {
                    $status = Password::sendResetLink(['email' => $record->email]);

                    if ($status === Password::RESET_LINK_SENT) {
                        Notification::make()
                            ->title('Email envoyé')
                            ->body('Un email de réinitialisation a été envoyé à ' . $record->email)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Impossible d\'envoyer l\'email de réinitialisation')
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('toggleStatus')
                ->label(fn ($record) => $record->actif ? 'Désactiver' : 'Activer')
                ->icon(fn ($record) => $record->actif ? 'heroicon-m-no-symbol' : 'heroicon-m-check-circle')
                ->color(fn ($record) => $record->actif ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn ($record) => ($record->actif ? 'Désactiver' : 'Activer') . ' l\'utilisateur')
                ->modalDescription(fn ($record) =>
                    $record->actif
                        ? 'L\'utilisateur ne pourra plus se connecter.'
                        : 'L\'utilisateur pourra à nouveau se connecter.'
                )
                ->action(function ($record) {
                    $record->update(['actif' => !$record->actif]);

                    Notification::make()
                        ->title('Statut mis à jour')
                        ->body('L\'utilisateur a été ' . ($record->actif ? 'activé' : 'désactivé'))
                        ->success()
                        ->send();
                }),


            Actions\DeleteAction::make()
                ->icon('heroicon-m-trash'),
        ];
    }
}
