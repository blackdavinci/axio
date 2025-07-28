<?php

namespace App\Filament\Resources\TypeCourrierResource\Pages;

use App\Filament\Resources\TypeCourrierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeCourrier extends EditRecord
{
    protected static string $resource = TypeCourrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
