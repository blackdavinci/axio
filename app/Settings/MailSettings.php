<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public string $from_name;
    public string $from_address;
    public ?string $signature;
    public bool $enable_notifications;
    public array $notification_types;
    public ?string $welcome_template;
    public ?string $reset_password_template;
    public int $notification_frequency;

    public static function group(): string
    {
        return 'mail';
    }

    public static function defaults(): array
    {
        return [
            'from_name' => 'République de Guinée',
            'from_address' => 'nepasrepondre@onpg.com',
            'signature' => "Cordialement,\nL'équipe d'administration\nRépublique de Guinée",
            'enable_notifications' => true,
            'notification_types' => ['email', 'in_app'],
            'welcome_template' => null,
            'reset_password_template' => null,
            'notification_frequency' => 15, // minutes
        ];
    }
}