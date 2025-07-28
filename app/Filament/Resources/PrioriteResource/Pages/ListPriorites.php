<?php

namespace App\Filament\Resources\PrioriteResource\Pages;

use App\Filament\Resources\PrioriteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPriorites extends ListRecords
{
    protected static string $resource = PrioriteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
