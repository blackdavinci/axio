<?php

namespace App\Notifications;

use App\Settings\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserMentionedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courrier;
    protected $mentionedBy;
    protected $context;

    /**
     * Create a new notification instance.
     */
    public function __construct($courrier, $mentionedBy, $context = 'courrier')
    {
        $this->courrier = $courrier;
        $this->mentionedBy = $mentionedBy;
        $this->context = $context;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $settings = app(NotificationSettings::class);
        $channels = [];

        if ($settings->enable_email_notifications && in_array('user_mentioned', $settings->notification_types_enabled)) {
            $channels[] = 'mail';
        }

        if ($settings->enable_in_app_notifications && in_array('user_mentioned', $settings->notification_types_enabled)) {
            $channels[] = 'database';
        }

        if ($settings->enable_sms_notifications && in_array('user_mentioned', $settings->notification_types_enabled)) {
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
            ->subject('Vous avez été mentionné - ' . $this->courrier->objet)
            ->greeting('Bonjour ' . $notifiable->fullName() . ',')
            ->line('Vous avez été mentionné dans un courrier par ' . $this->mentionedBy->fullName() . '.')
            ->line('**Objet du courrier :** ' . $this->courrier->objet)
            ->line('**Type :** ' . $this->courrier->typeCourrier->nom ?? 'Non défini')
            ->when($this->courrier->priorite, function ($message) {
                return $message->line('**Priorité :** ' . $this->courrier->priorite->nom);
            })
            ->when($this->courrier->date_limite, function ($message) {
                return $message->line('**Date limite :** ' . $this->courrier->date_limite->format('d/m/Y'));
            })
            ->line('**Mentionné par :** ' . $this->mentionedBy->fullName() . ' (' . $this->mentionedBy->fonction . ')')
            ->action('Voir le courrier', url('/admin/courriers/' . $this->courrier->id))
            ->salutation('Cordialement,');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_mentioned',
            'courrier_id' => $this->courrier->id,
            'courrier_objet' => $this->courrier->objet,
            'courrier_type' => $this->courrier->typeCourrier->nom ?? 'Non défini',
            'courrier_priorite' => $this->courrier->priorite->nom ?? 'Normale',
            'date_limite' => $this->courrier->date_limite?->format('d/m/Y'),
            'mentioned_by' => $this->mentionedBy->fullName(),
            'mentioned_by_id' => $this->mentionedBy->id,
            'mentioned_by_fonction' => $this->mentionedBy->fonction,
            'context' => $this->context,
            'url' => '/admin/courriers/' . $this->courrier->id,
            'icon' => 'heroicon-o-at-symbol',
            'color' => 'info',
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): array
    {
        $message = "Vous avez été mentionné :\n";
        $message .= "Courrier: " . $this->courrier->objet . "\n";
        $message .= "Par: " . $this->mentionedBy->fullName() . "\n";
        
        if ($this->courrier->date_limite) {
            $message .= "Échéance: " . $this->courrier->date_limite->format('d/m/Y');
        }

        return [
            'message' => $message
        ];
    }
}
