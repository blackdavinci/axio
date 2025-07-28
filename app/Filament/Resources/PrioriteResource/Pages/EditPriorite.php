<?php

namespace App\Filament\Resources\PrioriteResource\Pages;

use App\Filament\Resources\PrioriteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPriorite extends EditRecord
{
    protected static string $resource = PrioriteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
