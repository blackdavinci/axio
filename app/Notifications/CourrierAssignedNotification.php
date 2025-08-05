<?php

namespace App\Notifications;

use App\Settings\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourrierAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courrier;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct($courrier, $assignment = null)
    {
        $this->courrier = $courrier;
        $this->assignedBy = $assignment ? $assignment->assignedBy : null;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $settings = app(NotificationSettings::class);
        $channels = [];

        if ($settings->enable_email_notifications && in_array('courrier_assigned', $settings->notification_types_enabled)) {
            $channels[] = 'mail';
        }

        if ($settings->enable_in_app_notifications && in_array('courrier_assigned', $settings->notification_types_enabled)) {
            $channels[] = 'database';
        }

        if ($settings->enable_sms_notifications && in_array('courrier_assigned', $settings->notification_types_enabled)) {
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
            ->subject('Nouveau courrier assigné - ' . $this->courrier->objet)
            ->greeting('Bonjour ' . $notifiable->fullName() . ',')
            ->line('Un nouveau courrier vous a été assigné.')
            ->line('**Objet :** ' . $this->courrier->objet)
            ->line('**Type :** ' . $this->courrier->typeCourrier->nom ?? 'Non défini')
            ->line('**Priorité :** ' . $this->courrier->priorite->nom ?? 'Normale')
            ->when($this->courrier->date_limite, function ($message) {
                return $message->line('**Date limite :** ' . $this->courrier->date_limite->format('d/m/Y'));
            })
            ->when($this->assignedBy, function ($message) {
                return $message->line('**Assigné par :** ' . $this->assignedBy->fullName());
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
            'type' => 'courrier_assigned',
            'courrier_id' => $this->courrier->id,
            'courrier_objet' => $this->courrier->objet,
            'courrier_type' => $this->courrier->typeCourrier->nom ?? 'Non défini',
            'courrier_priorite' => $this->courrier->priorite->nom ?? 'Normale',
            'date_limite' => $this->courrier->date_limite?->format('d/m/Y'),
            'assigned_by' => $this->assignedBy?->fullName(),
            'assigned_by_id' => $this->assignedBy?->id,
            'url' => '/admin/courriers/' . $this->courrier->id,
            'icon' => 'heroicon-o-inbox-arrow-down',
            'color' => 'primary',
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): array
    {
        $message = "Nouveau courrier assigné :\n";
        $message .= "Objet: " . $this->courrier->objet . "\n";
        $message .= "Type: " . ($this->courrier->typeCourrier->nom ?? 'Non défini') . "\n";
        
        if ($this->courrier->date_limite) {
            $message .= "Échéance: " . $this->courrier->date_limite->format('d/m/Y') . "\n";
        }
        
        if ($this->assignedBy) {
            $message .= "Par: " . $this->assignedBy->fullName();
        }

        return [
            'message' => $message
        ];
    }
}
