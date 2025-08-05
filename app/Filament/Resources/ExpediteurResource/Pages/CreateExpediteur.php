<?php

namespace App\Filament\Resources\ExpediteurResource\Pages;

use App\Filament\Resources\ExpediteurResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpediteur extends CreateRecord
{
    protected static string $resource = ExpediteurResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
