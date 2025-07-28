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
        // Initialiser les nouveaux settings GeneralSettings
        $newSettings = [
            'general.organization_short_name' => 'RG',
            'general.enable_document_archiving' => true,
            'general.document_retention_years' => 10,
            'general.auto_archive_after_retention' => false,
            'general.archive_storage_path' => 'archives',
            'general.compress_archived_documents' => true,
            'general.archivable_document_types' => ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'],
        ];

        foreach ($newSettings as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['group' => 'general', 'name' => str_replace('general.', '', $key)],
                ['payload' => json_encode($value), 'locked' => false]
            );
        }

        // Initialiser les RecoverySettings
        $recoverySettings = [
            'recovery.recovery_reminder1_days' => 15,
            'recovery.recovery_reminder2_days' => 30,
            'recovery.recovery_mise_en_demeure_days' => 15,
            'recovery.recovery_litigation_threshold_amount' => 10000.00,
            'recovery.mise_en_demeure_template_text' => "Madame, Monsieur,\n\nNous vous informons que malgré nos précédents rappels, votre créance d'un montant de [MONTANT] demeure impayée.\n\nVous disposez d'un délai de 30 jours à compter de la réception de cette mise en demeure pour régulariser votre situation.\n\nÀ défaut de paiement dans ce délai, nous nous verrons contraints d'engager une procédure de recouvrement judiciaire.\n\nCordialement,\nLe service de recouvrement",
            'recovery.enable_automatic_reminders' => true,
            'recovery.send_email_notifications' => true,
            'recovery.send_sms_notifications' => false,
            'recovery.enable_late_payment_interest' => true,
            'recovery.late_payment_interest_rate' => 12.0,
            'recovery.auto_escalate_to_litigation' => false,
            'recovery.litigation_contact_email' => 'contentieux@gouv.gn',
        ];

        foreach ($recoverySettings as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['group' => 'recovery', 'name' => str_replace('recovery.', '', $key)],
                ['payload' => json_encode($value), 'locked' => false]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les nouveaux settings ajoutés
        \DB::table('settings')->where('group', 'recovery')->delete();
        
        $newGeneralFields = ['organization_short_name', 'enable_document_archiving', 'document_retention_years', 
                           'auto_archive_after_retention', 'archive_storage_path', 'compress_archived_documents', 
                           'archivable_document_types'];
        
        \DB::table('settings')->where('group', 'general')
                              ->whereIn('name', $newGeneralFields)
                              ->delete();
    }
};
