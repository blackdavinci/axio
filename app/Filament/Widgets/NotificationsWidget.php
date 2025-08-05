<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class NotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.notifications-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    public function getNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }

        return $user->unreadNotifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;
                
                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'generic',
                    'title' => $this->getNotificationTitle($data),
                    'message' => $this->getNotificationMessage($data),
                    'icon' => $data['icon'] ?? 'heroicon-o-bell',
                    'color' => $data['color'] ?? 'primary',
                    'url' => $data['url'] ?? null,
                    'created_at' => $notification->created_at,
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        return $user ? $user->unreadNotifications()->count() : 0;
    }

    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        if ($user) {
            $notification = $user->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
                
                Notification::make()
                    ->title('Notification marquée comme lue')
                    ->success()
                    ->send();
            }
        }
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications()->update(['read_at' => now()]);
            
            Notification::make()
                ->title('Toutes les notifications ont été marquées comme lues')
                ->success()
                ->send();
        }
    }

    #[On('notification-received')]
    public function refreshNotifications()
    {
        // Rafraîchir le widget quand une nouvelle notification arrive
    }

    protected function getNotificationTitle(array $data): string
    {
        return match ($data['type'] ?? 'generic') {
            'courrier_assigned' => 'Courrier assigné',
            'status_changed' => 'Statut modifié',
            'deadline_reminder' => $this->getDeadlineTitle($data),
            'escalation' => 'Escalade',
            'user_mentioned' => 'Vous avez été mentionné',
            'task_completed' => 'Tâche terminée',
            default => 'Notification',
        };
    }

    protected function getNotificationMessage(array $data): string
    {
        return match ($data['type'] ?? 'generic') {
            'courrier_assigned' => ($data['courrier_objet'] ?? '') . 
                ($data['assigned_by'] ? ' (par ' . $data['assigned_by'] . ')' : ''),
            'status_changed' => ($data['courrier_objet'] ?? '') . ' : ' . 
                ($data['old_status_label'] ?? '') . ' → ' . ($data['new_status_label'] ?? ''),
            'deadline_reminder' => ($data['courrier_objet'] ?? '') . 
                ' (échéance: ' . ($data['time_remaining_text'] ?? '') . ')',
            'escalation' => $data['courrier_objet'] ?? 'Escalade hiérarchique',
            'user_mentioned' => 'Vous avez été mentionné dans: ' . ($data['courrier_objet'] ?? ''),
            'task_completed' => $data['task_name'] ?? 'Une tâche a été terminée',
            default => $data['message'] ?? 'Nouvelle notification',
        };
    }

    protected function getDeadlineTitle(array $data): string
    {
        $urgencyLevel = $data['urgency_level'] ?? 'reminder';
        
        return match ($urgencyLevel) {
            'overdue' => '🚨 ÉCHÉANCE DÉPASSÉE',
            'urgent' => '⚠️ ÉCHÉANCE AUJOURD\'HUI',
            default => '📅 Rappel d\'échéance',
        };
    }
}
