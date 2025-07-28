<?php

namespace App\Filament\Resources\TypeCourrierResource\Pages;

use App\Filament\Resources\TypeCourrierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeCourriers extends ListRecords
{
    protected static string $resource = TypeCourrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
