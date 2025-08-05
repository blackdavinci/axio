<?php

namespace App\Notifications;

use App\Settings\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class DeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courrier;
    protected $daysRemaining;

    /**
     * Create a new notification instance.
     */
    public function __construct($courrier, $daysRemaining = null)
    {
        $this->courrier = $courrier;
        $this->daysRemaining = $daysRemaining ?? Carbon::parse($courrier->date_limite)->diffInDays(now());
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $settings = app(NotificationSettings::class);
        $channels = [];

        if ($settings->enable_email_notifications && in_array('deadline_reminder', $settings->notification_types_enabled)) {
            $channels[] = 'mail';
        }

        if ($settings->enable_in_app_notifications && in_array('deadline_reminder', $settings->notification_types_enabled)) {
            $channels[] = 'database';
        }

        if ($settings->enable_sms_notifications && in_array('deadline_reminder', $settings->notification_types_enabled)) {
            $channels[] = \App\Notifications\Channels\SmsChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $urgencyLevel = $this->getUrgencyLevel();
        
        return (new MailMessage)
            ->subject($urgencyLevel['subject'] . ' - ' . $this->courrier->objet)
            ->greeting('Bonjour ' . $notifiable->fullName() . ',')
            ->line($urgencyLevel['message'])
            ->line('**Objet :** ' . $this->courrier->objet)
            ->line('**Type :** ' . $this->courrier->typeCourrier->nom ?? 'Non défini')
            ->line('**Date limite :** ' . $this->courrier->date_limite->format('d/m/Y à H:i'))
            ->line('**Temps restant :** ' . $this->getTimeRemainingText())
            ->when($this->courrier->priorite, function ($message) {
                return $message->line('**Priorité :** ' . $this->courrier->priorite->nom);
            })
            ->action('Traiter le courrier', url('/admin/courriers/' . $this->courrier->id))
            ->salutation('Cordialement,');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $urgencyLevel = $this->getUrgencyLevel();
        
        return [
            'type' => 'deadline_reminder',
            'courrier_id' => $this->courrier->id,
            'courrier_objet' => $this->courrier->objet,
            'date_limite' => $this->courrier->date_limite->format('d/m/Y H:i'),
            'days_remaining' => $this->daysRemaining,
            'time_remaining_text' => $this->getTimeRemainingText(),
            'urgency_level' => $urgencyLevel['level'],
            'url' => '/admin/courriers/' . $this->courrier->id,
            'icon' => $urgencyLevel['icon'],
            'color' => $urgencyLevel['color'],
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): array
    {
        $urgencyLevel = $this->getUrgencyLevel();
        
        $message = $urgencyLevel['short_message'] . "\n";
        $message .= "Courrier: " . $this->courrier->objet . "\n";
        $message .= "Échéance: " . $this->courrier->date_limite->format('d/m/Y H:i') . "\n";
        $message .= "Temps restant: " . $this->getTimeRemainingText();

        return [
            'message' => $message
        ];
    }

    /**
     * Get urgency level based on remaining time
     */
    protected function getUrgencyLevel(): array
    {
        if ($this->daysRemaining <= 0) {
            return [
                'level' => 'overdue',
                'subject' => '🚨 ÉCHÉANCE DÉPASSÉE',
                'message' => 'L\'échéance de ce courrier a été dépassée. Une action urgente est requise.',
                'short_message' => '🚨 ÉCHÉANCE DÉPASSÉE!',
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'danger',
            ];
        } elseif ($this->daysRemaining <= 1) {
            return [
                'level' => 'urgent',
                'subject' => '⚠️ ÉCHÉANCE AUJOURD\'HUI',
                'message' => 'L\'échéance de ce courrier est aujourd\'hui. Veuillez le traiter rapidement.',
                'short_message' => '⚠️ ÉCHÉANCE AUJOURD\'HUI',
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
            ];
        } else {
            return [
                'level' => 'reminder',
                'subject' => '📅 Rappel d\'échéance',
                'message' => 'L\'échéance de ce courrier approche. Veuillez prévoir son traitement.',
                'short_message' => '📅 Rappel d\'échéance',
                'icon' => 'heroicon-o-calendar-days',
                'color' => 'info',
            ];
        }
    }

    /**
     * Get human-readable time remaining text
     */
    protected function getTimeRemainingText(): string
    {
        if ($this->daysRemaining <= 0) {
            $hoursOverdue = now()->diffInHours($this->courrier->date_limite);
            return "Dépassée de {$hoursOverdue}h";
        } elseif ($this->daysRemaining < 1) {
            $hoursRemaining = now()->diffInHours($this->courrier->date_limite);
            return "{$hoursRemaining} heures";
        } else {
            return "{$this->daysRemaining} jour" . ($this->daysRemaining > 1 ? 's' : '');
        }
    }
}
