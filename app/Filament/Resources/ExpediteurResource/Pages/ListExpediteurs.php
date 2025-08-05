<?php

namespace App\Filament\Resources\ExpediteurResource\Pages;

use App\Filament\Resources\ExpediteurResource;
use App\Models\Expediteur;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ListExpediteurs extends ListRecords
{
    protected static string $resource = ExpediteurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Ajouter un expéditeur')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
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
                ->action(function (array $data) {
                    $expediteur = Expediteur::create($data);
                    $expediteur->save();
                }),
        ];
    }
}
