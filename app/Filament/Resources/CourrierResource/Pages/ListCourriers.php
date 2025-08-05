<?php

namespace App\Filament\Resources\CourrierResource\Pages;

use App\Filament\Resources\CourrierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourriers extends ListRecords
{
    protected static string $resource = CourrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Enregistrer un courrier')
                ->color('success')
                ->icon('heroicon-o-inbox-arrow-down'),
        ];
    }
}
