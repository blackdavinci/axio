<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Actions\Action; // Pour les actions de page

class NotificationsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $title = 'Mes notifications';
    protected static ?string $navigationLabel = 'Notifications';
    protected static string $view = 'filament.pages.notifications-page';

    // Pour rafraîchir la page toutes les X secondes (optionnel)
    protected static ?string $pollingInterval = '5s';

    public function getNotificationsProperty()
    {
        return Auth::user()->notifications()->latest()->paginate(10); // Paginer les notifications
    }

    public function markAsRead(string $notificationId)
    {
        Auth::user()->notifications()->find($notificationId)->markAsRead();

        Notification::make()
            ->title('Notification marquée comme lue.')
            ->success()
            ->send();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        Notification::make()
            ->title('Toutes les notifications ont été marquées comme lues.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label('Marquer tout comme lu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(fn () => $this->markAllAsRead()),
        ];
    }
}
