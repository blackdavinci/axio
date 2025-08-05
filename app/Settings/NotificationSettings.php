<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    // Paramètres généraux
    public bool $enable_notifications;
    public array $default_channels; // ['mail', 'database', 'sms']
    public bool $enable_email_notifications;
    public bool $enable_sms_notifications;
    public bool $enable_in_app_notifications;
    
    // NimbaSMS Configuration
    public string $nimba_api_url;
    public string $nimba_api_key;
    public string $nimba_sender_id;
    public bool $nimba_test_mode;
    
    // Templates et contenu
    public string $notification_signature;
    public array $notification_types_enabled; // Types de notifications activés
    
    // Règles métier
    public bool $notify_on_courrier_assignment;
    public bool $notify_on_status_change;
    public bool $notify_on_deadline_approaching;
    public int $deadline_reminder_days; // Jours avant échéance pour rappel
    public bool $notify_on_escalation;
    
    // Paramètres de fréquence
    public int $max_sms_per_day_per_user;
    public bool $group_notifications; // Grouper les notifications similaires

    public static function group(): string
    {
        return 'notification';
    }

    public static function defaults(): array
    {
        return [
            // Général
            'enable_notifications' => true,
            'default_channels' => ['mail', 'database'],
            'enable_email_notifications' => true,
            'enable_sms_notifications' => false,
            'enable_in_app_notifications' => true,
            
            // NimbaSMS
            'nimba_api_url' => 'https://api.nimbasms.com/v1',
            'nimba_api_key' => '',
            'nimba_sender_id' => 'AXIO',
            'nimba_test_mode' => true,
            
            // Templates
            'notification_signature' => "Cordialement,\nAxio - République de Guinée",
            'notification_types_enabled' => [
                'courrier_assigned',
                'status_changed', 
                'deadline_reminder',
                'escalation'
            ],
            
            // Règles métier
            'notify_on_courrier_assignment' => true,
            'notify_on_status_change' => true,
            'notify_on_deadline_approaching' => true,
            'deadline_reminder_days' => 2,
            'notify_on_escalation' => true,
            
            // Limites
            'max_sms_per_day_per_user' => 10,
            'group_notifications' => true,
        ];
    }
}