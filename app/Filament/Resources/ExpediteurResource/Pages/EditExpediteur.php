<?php

namespace App\Filament\Resources\ExpediteurResource\Pages;

use App\Filament\Resources\ExpediteurResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpediteur extends EditRecord
{
    protected static string $resource = ExpediteurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
