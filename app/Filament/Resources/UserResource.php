<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Administration';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Photo')
                            ->image()
                            ->imageEditor()
                            ->avatar()
                            ->disk('public')
                            ->directory('users/photos')
                            ->visibility('public')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('prenom')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('genre')
                            ->label('Genre')
                            ->options([
                                'M' => 'Masculin',
                                'F' => 'Féminin',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe (optionnel)')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(6)
                            ->helperText('Laissez vide pour générer un mot de passe automatique'),

                        PhoneInput::make('telephone')
                            ->label('Téléphone principal')
                            ->defaultCountry('GN')
                            ->validateFor(lenient: true)
                            ->unique(ignoreRecord: true)
                            ->required(),

                        PhoneInput::make('telephone_secondaire')
                            ->label('Téléphone secondaire')
                            ->defaultCountry('GN')
                            ->validateFor(lenient: true),

                        Forms\Components\TextInput::make('matricule')
                            ->label('Matricule')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('grade')
                            ->label('Grade')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('adresse')
                            ->label('Adresse')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Informations complémentaires')
                    ->schema([
                        Forms\Components\DatePicker::make('date_naissance')
                            ->label('Date de naissance')
                            ->maxDate(now()->subYears(16)),

                        Forms\Components\Select::make('categorie')
                            ->label('Catégorie')
                            ->options([
                                'fonctionnaire' => 'Fonctionnaire',
                                'contractuel' => 'Contractuel',
                                'consultant' => 'Consultant',
                                'stagiaire' => 'Stagiaire',
                            ]),

                        Forms\Components\TextInput::make('specialite')
                            ->label('Spécialité')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('personne_urgence')
                            ->label('Personne à contacter en urgence')
                            ->maxLength(255),

                        PhoneInput::make('telephone_urgence')
                            ->label('Téléphone urgence')
                            ->defaultCountry('GN')
                            ->validateFor(lenient: true),

                        Forms\Components\TextInput::make('poste')
                            ->label('Poste/Fonction')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Affectation')
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->label('Service')
                            ->relationship('service', 'nom')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nom')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(10),
                                Forms\Components\Textarea::make('description'),
                            ]),

                        Forms\Components\Select::make('role')
                            ->label('Rôle')
                            ->options(fn () => \Spatie\Permission\Models\Role::pluck('name', 'name'))
                            ->afterStateUpdated(function ($state, $record) {
                                if ($record && $state) {
                                    $record->syncRoles([$state]);
                                }
                            })
                            ->dehydrated(false)
                            ->default(fn ($record) => $record?->roles?->first()?->name)
                            ->preload()
                            ->searchable()
                            ->required(),

                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->disk('public')
                    ->visibility('public')
                    ->defaultImageUrl(asset('images/default-avatar.svg')),

                Tables\Columns\TextColumn::make('fullName')
                    ->label('Nom complet')
                    ->getStateUsing(fn ($record) => $record->fullName())
                    ->searchable(['prenom', 'nom'])
                    ->sortable(['prenom', 'nom']),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('service.nom')
                    ->label('Service')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('poste')
                    ->label('Poste')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôle')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Filters\SelectFilter::make('service')
                    ->relationship('service', 'nom')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('actif')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-m-eye'),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-m-pencil'),

                Tables\Actions\Action::make('resetPassword')
                    ->label('Reset MDP')
                    ->icon('heroicon-m-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $record->email]);

                        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
                            \Filament\Notifications\Notification::make()
                                ->title('Email de réinitialisation envoyé')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Erreur lors de l\'envoi')
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('toggleStatus')
                    ->label(fn ($record) => $record->actif ? 'Désactiver' : 'Activer')
                    ->icon(fn ($record) => $record->actif ? 'heroicon-m-no-symbol' : 'heroicon-m-check-circle')
                    ->color(fn ($record) => $record->actif ? 'danger' : 'success')
                    ->action(function ($record) {
                        $record->update(['actif' => !$record->actif]);
                        \Filament\Notifications\Notification::make()
                            ->title('Statut mis à jour')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-m-trash'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
