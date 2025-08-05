<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insérer directement les valeurs par défaut dans la table settings
        $defaults = [
            // Général
            ['group' => 'notification', 'name' => 'enable_notifications', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notification', 'name' => 'default_channels', 'payload' => json_encode(['mail', 'database']), 'locked' => false],
            ['group' => 'notification', 'name' => 'enable_email_notifications', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notification', 'name' => 'enable_sms_notifications', 'payload' => json_encode(false), 'locked' => false],
            ['group' => 'notification', 'name' => 'enable_in_app_notifications', 'payload' => json_encode(true), 'locked' => false],
            
            // NimbaSMS
            ['group' => 'notification', 'name' => 'nimba_api_url', 'payload' => json_encode('https://api.nimbasms.com/v1'), 'locked' => false],
            ['group' => 'notification', 'name' => 'nimba_api_key', 'payload' => json_encode(''), 'locked' => false],
            ['group' => 'notification', 'name' => 'nimba_sender_id', 'payload' => json_encode('AXIO'), 'locked' => false],
            ['group' => 'notification', 'name' => 'nimba_test_mode', 'payload' => json_encode(true), 'locked' => false],
            
            // Templates
            ['group' => 'notification', 'name' => 'notification_signature', 'payload' => json_encode("Cordialement,\nAxio - République de Guinée"), 'locked' => false],
            ['group' => 'notification', 'name' => 'notification_types_enabled', 'payload' => json_encode(['courrier_assigned', 'status_changed', 'deadline_reminder', 'escalation']), 'locked' => false],
            
            // Règles métier
            ['group' => 'notification', 'name' => 'notify_on_courrier_assignment', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notification', 'name' => 'notify_on_status_change', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notification', 'name' => 'notify_on_deadline_approaching', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notification', 'name' => 'deadline_reminder_days', 'payload' => json_encode(2), 'locked' => false],
            ['group' => 'notification', 'name' => 'notify_on_escalation', 'payload' => json_encode(true), 'locked' => false],
            
            // Limites
            ['group' => 'notification', 'name' => 'max_sms_per_day_per_user', 'payload' => json_encode(10), 'locked' => false],
            ['group' => 'notification', 'name' => 'group_notifications', 'payload' => json_encode(true), 'locked' => false],
        ];

        foreach ($defaults as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'name' => $setting['name']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer tous les paramètres de notification
        DB::table('settings')->where('group', 'notification')->delete();
    }
};
