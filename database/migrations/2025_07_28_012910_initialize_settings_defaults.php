<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Initialiser les settings avec leurs valeurs par défaut
        $this->initializeGeneralSettings();
        $this->initializeMailSettings();
        $this->initializeCourrierSettings();
        $this->initializeSecuritySettings();
        $this->initializeBreezySettings();
    }

    private function initializeGeneralSettings(): void
    {
        $settings = [
            'general.organization_name' => 'République de Guinée',
            'general.organization_logo' => null,
            'general.organization_favicon' => null,
            'general.organization_address' => 'Conakry, République de Guinée',
            'general.organization_phone' => '+224',
            'general.organization_email' => 'contact@gouv.gn',
            'general.timezone' => 'Africa/Conakry',
            'general.language' => 'fr',
            'general.date_format' => 'd/m/Y',
            'general.organization_website' => null,
            'general.organization_description' => null,
        ];

        foreach ($settings as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['group' => 'general', 'name' => str_replace('general.', '', $key)],
                ['payload' => json_encode($value), 'locked' => false]
            );
        }
    }

    private function initializeMailSettings(): void
    {
        $settings = [
            'mail.from_name' => 'République de Guinée',
            'mail.from_address' => 'nepasrepondre@onpg.com',
            'mail.signature' => "Cordialement,\nL'équipe d'administration\nRépublique de Guinée",
            'mail.enable_notifications' => true,
            'mail.notification_types' => ['email', 'in_app'],
            'mail.welcome_template' => null,
            'mail.reset_password_template' => null,
            'mail.notification_frequency' => 15,
        ];

        foreach ($settings as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['group' => 'mail', 'name' => str_replace('mail.', '', $key)],
                ['payload' => json_encode($value), 'locked' => false]
            );
        }
    }

    private function initializeCourrierSettings(): void
    {
        $settings = [
            'courrier.courrier_entrant_prefix' => 'CE',
            'courrier.courrier_sortant_prefix' => 'CS',
            'courrier.courrier_entrant_counter' => 1,
            'courrier.courrier_sortant_counter' => 1,
            'courrier.numero_format' => '{prefix}-{year}-{counter:4}',
            'courrier.delai_traitement_standard' => 7,
            'courrier.delai_traitement_urgent' => 2,
            'courrier.delai_escalade' => 5,
            'courrier.auto_attribution' => true,
            'courrier.niveaux_priorite' => [
                'faible' => ['label' => 'Faible', 'color' => 'gray', 'delai' => 15],
                'normale' => ['label' => 'Normale', 'color' => 'blue', 'delai' => 7],
                'haute' => ['label' => 'Haute', 'color' => 'orange', 'delai' => 3],
                'urgente' => ['label' => 'Urgente', 'color' => 'red', 'delai' => 1],
            ],
        ];

        foreach ($settings as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['group' => 'courrier', 'name' => str_replace('courrier.', '', $key)],
                ['payload' => json_encode($value), 'locked' => false]
            );
        }
    }

    private function initializeSecuritySettings(): void
    {
        $settings = [
            'security.password_min_length' => 8,
            'security.password_require_uppercase' => true,
            'security.password_require_lowercase' => true,
            'security.password_require_numbers' => true,
            'security.password_require_symbols' => false,
            'security.password_expiry_days' => 90,
            'security.session_lifetime' => 120,
            'security.max_login_attempts' => 5,
            'security.lockout_duration' => 15,
            'security.force_password_reset' => false,
            'security.enable_2fa' => false,
        ];

        foreach ($settings as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['group' => 'security', 'name' => str_replace('security.', '', $key)],
                ['payload' => json_encode($value), 'locked' => false]
            );
        }
    }

    private function initializeBreezySettings(): void
    {
        $settings = [
            'breezy.enable_registration' => false,
            'breezy.enable_password_reset' => true,
            'breezy.enable_profile_page' => true,
            'breezy.force_email_verification' => false,
            'breezy.sanctum_abilities' => ['*'],
        ];

        foreach ($settings as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['group' => 'breezy', 'name' => str_replace('breezy.', '', $key)],
                ['payload' => json_encode($value), 'locked' => false]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer tous les settings initialisés
        \DB::table('settings')->whereIn('group', ['general', 'mail', 'courrier', 'security', 'breezy'])->delete();
    }
};
