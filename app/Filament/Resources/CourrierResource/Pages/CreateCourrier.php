<?php

namespace App\Filament\Resources\CourrierResource\Pages;

use App\Filament\Resources\CourrierResource;
use App\Notifications\CourrierAssignedNotification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourrier extends CreateRecord
{
    protected static string $resource = CourrierResource::class;

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Enregistrer un courrier')
            ->color('primary');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $courrier = $this->record;

        // Notifier l'utilisateur assigné
        if ($courrier->user_id && $courrier->user_id !== auth()->id()) {
            $courrier->user->notify(new CourrierAssignedNotification($courrier, auth()->user()));
        }

        // Notifier les utilisateurs mentionnés
        if ($courrier->mentions) {
            $courrier->notifyMentionedUsers(auth()->user());
        }
    }
}
