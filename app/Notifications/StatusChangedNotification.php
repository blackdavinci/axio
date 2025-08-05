<?php

namespace App\Notifications;

use App\Settings\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courrier;
    protected $oldStatus;
    protected $newStatus;
    protected $changedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct($courrier, $oldStatus, $newStatus, $changedBy = null)
    {
        $this->courrier = $courrier;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $settings = app(NotificationSettings::class);
        $channels = [];

        if ($settings->enable_email_notifications && in_array('status_changed', $settings->notification_types_enabled)) {
            $channels[] = 'mail';
        }

        if ($settings->enable_in_app_notifications && in_array('status_changed', $settings->notification_types_enabled)) {
            $channels[] = 'database';
        }

        if ($settings->enable_sms_notifications && in_array('status_changed', $settings->notification_types_enabled)) {
            $channels[] = \App\Notifications\Channels\SmsChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Changement de statut - ' . $this->courrier->objet)
            ->greeting('Bonjour ' . $notifiable->fullName() . ',')
            ->line('Le statut d\'un courrier a été modifié.')
            ->line('**Objet :** ' . $this->courrier->objet)
            ->line('**Ancien statut :** ' . $this->getStatusLabel($this->oldStatus))
            ->line('**Nouveau statut :** ' . $this->getStatusLabel($this->newStatus))
            ->when($this->changedBy, function ($message) {
                return $message->line('**Modifié par :** ' . $this->changedBy->fullName());
            })
            ->action('Voir le courrier', url('/admin/courriers/' . $this->courrier->id))
            ->salutation('Cordialement,');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'status_changed',
            'courrier_id' => $this->courrier->id,
            'courrier_objet' => $this->courrier->objet,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'old_status_label' => $this->getStatusLabel($this->oldStatus),
            'new_status_label' => $this->getStatusLabel($this->newStatus),
            'changed_by' => $this->changedBy?->fullName(),
            'changed_by_id' => $this->changedBy?->id,
            'url' => '/admin/courriers/' . $this->courrier->id,
            'icon' => 'heroicon-o-arrow-path',
            'color' => $this->getStatusColor($this->newStatus),
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): array
    {
        $message = "Changement de statut :\n";
        $message .= "Courrier: " . $this->courrier->objet . "\n";
        $message .= "Statut: " . $this->getStatusLabel($this->oldStatus) . " → " . $this->getStatusLabel($this->newStatus);
        
        if ($this->changedBy) {
            $message .= "\nPar: " . $this->changedBy->fullName();
        }

        return [
            'message' => $message
        ];
    }

    /**
     * Get status label for display
     */
    protected function getStatusLabel($status): string
    {
        $labels = [
            'recu' => 'Reçu',
            'en_cours' => 'En cours',
            'traite' => 'Traité',
            'archive' => 'Archivé',
            'rejete' => 'Rejeté',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get status color for notification
     */
    protected function getStatusColor($status): string
    {
        $colors = [
            'recu' => 'info',
            'en_cours' => 'warning',
            'traite' => 'success',
            'archive' => 'gray',
            'rejete' => 'danger',
        ];

        return $colors[$status] ?? 'primary';
    }
}
