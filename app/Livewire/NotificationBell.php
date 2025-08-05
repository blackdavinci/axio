<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public $showDropdown = false;

    public function getNotificationsProperty()
    {
        return Auth::user()
            ->unreadNotifications()
            ->latest()
            ->limit(5)
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
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });
    }

    public function getUnreadCountProperty()
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function markAsRead($notificationId)
    {
        Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->update(['read_at' => now()]);

        $this->dispatch('notification-read');
    }

    public function markAllAsRead()
    {
        Auth::user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        $this->dispatch('notifications-cleared');
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
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
            'courrier_assigned' => ($data['courrier_objet'] ?? ''),
            'status_changed' => ($data['courrier_objet'] ?? '') . ' : ' . 
                ($data['old_status_label'] ?? '') . ' → ' . ($data['new_status_label'] ?? ''),
            'deadline_reminder' => ($data['courrier_objet'] ?? ''),
            'escalation' => $data['courrier_objet'] ?? 'Escalade hiérarchique',
            'user_mentioned' => 'Dans: ' . ($data['courrier_objet'] ?? ''),
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

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
