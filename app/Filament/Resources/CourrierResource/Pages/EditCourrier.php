<?php

namespace App\Filament\Resources\CourrierResource\Pages;

use App\Filament\Resources\CourrierResource;
use App\Notifications\CourrierAssignedNotification;
use App\Notifications\StatusChangedNotification;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourrier extends EditRecord
{
    protected static string $resource = CourrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $courrier = $this->record;

        // Vérifier si l'utilisateur assigné a changé
        if ($this->record->isDirty('user_id') && $courrier->user_id !== auth()->id()) {
            $courrier->user->notify(new CourrierAssignedNotification($courrier, auth()->user()));
        }

        // Vérifier si le statut a changé
        if ($this->record->isDirty('statut')) {
            $oldStatus = $this->record->getOriginal('statut');
            $newStatus = $this->record->statut;

            if ($courrier->user_id !== auth()->id()) {
                $courrier->user->notify(new StatusChangedNotification($courrier, $oldStatus, $newStatus, auth()->user()));
            }
        }

        // Notifier les nouveaux utilisateurs mentionnés
        if ($this->record->isDirty('mentions') && $courrier->mentions) {
            $courrier->notifyMentionedUsers(auth()->user());
        }
    }
}
