<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpediteurResource\Pages;
use App\Filament\Resources\ExpediteurResource\RelationManagers;
use App\Models\Expediteur;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ExpediteurResource extends Resource
{
    protected static ?string $model = Expediteur::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Expéditeurs';

    protected static ?string $navigationGroup = 'Gestion documentaire';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations expéditeur')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'personne' => 'Personne',
                                'entreprise' => 'Entreprise',
                                'administration' => 'Administration',
                            ])
                            ->default('personne')
                            ->required(),

                        PhoneInput::make('telephone')
                            ->label('Téléphone')
                            ->defaultCountry('GN')
                            ->validateFor(lenient: true)
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('adresse')
                            ->label('Adresse')
                            ->rows(3),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'personne' => 'Personne',
                        'entreprise' => 'Entreprise',
                        'administration' => 'Administration',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'personne',
                        'success' => 'entreprise',
                        'warning' => 'administration',
                    ]),

                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courriers_count')
                    ->label('Courriers')
                    ->counts('courriers')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'personne' => 'Personne',
                        'entreprise' => 'Entreprise',
                        'administration' => 'Administration',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('Modifier')
                    ->color('success')
                    ->icon('heroicon-m-pencil-square')
                    ->fillForm(fn (Expediteur $record): array => [
                        'nom' => $record->nom,
                        'type' => $record->type,
                        'telephone' => $record->telephone,
                        'email' => $record->email,
                        'adresse' => $record->adresse,
                    ])
                    ->form([
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
                    ->action(function (array $data, Expediteur $record): void {
                        $record->update($data);
                        $record->save();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListExpediteurs::route('/'),
            'create' => Pages\CreateExpediteur::route('/create'),
            'edit' => Pages\EditExpediteur::route('/{record}/edit'),
        ];
    }
}
