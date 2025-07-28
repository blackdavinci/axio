<?php

namespace App\Filament\Resources\TypeDocumentResource\Pages;

use App\Filament\Resources\TypeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeDocument extends EditRecord
{
    protected static string $resource = TypeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
