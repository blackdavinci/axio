<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BreezySettings extends Settings
{
    public bool $enable_registration;
    public bool $enable_password_reset;
    public bool $enable_profile_page;
    public bool $force_email_verification;
    public array $sanctum_abilities;

    public static function group(): string
    {
        return 'breezy';
    }

    public static function defaults(): array
    {
        return [
            'enable_registration' => false, // Désactivé par défaut pour admin
            'enable_password_reset' => true,
            'enable_profile_page' => true,
            'force_email_verification' => false,
            'sanctum_abilities' => ['*'],
        ];
    }
}