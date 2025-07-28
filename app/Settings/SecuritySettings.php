<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SecuritySettings extends Settings
{
    public int $password_min_length;
    public bool $password_require_uppercase;
    public bool $password_require_lowercase;
    public bool $password_require_numbers;
    public bool $password_require_symbols;
    public int $password_expiry_days;
    public int $session_lifetime;
    public int $max_login_attempts;
    public int $lockout_duration;
    public bool $force_password_reset;
    public bool $enable_2fa;

    public static function group(): string
    {
        return 'security';
    }

    public static function defaults(): array
    {
        return [
            'password_min_length' => 8,
            'password_require_uppercase' => true,
            'password_require_lowercase' => true,
            'password_require_numbers' => true,
            'password_require_symbols' => false,
            'password_expiry_days' => 90,
            'session_lifetime' => 120, // minutes
            'max_login_attempts' => 5,
            'lockout_duration' => 15, // minutes
            'force_password_reset' => false,
            'enable_2fa' => false,
        ];
    }
}