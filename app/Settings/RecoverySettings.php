<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RecoverySettings extends Settings
{
    // Délais des rappels
    public int $recovery_reminder1_days;
    public int $recovery_reminder2_days;
    public int $recovery_mise_en_demeure_days;
    
    // Seuil pour contentieux
    public float $recovery_litigation_threshold_amount;
    
    // Texte par défaut des mises en demeure
    public string $mise_en_demeure_template_text;
    
    // Paramètres de notification
    public bool $enable_automatic_reminders;
    public bool $send_email_notifications;
    public bool $send_sms_notifications;
    
    // Paramètres de calcul des intérêts de retard
    public bool $enable_late_payment_interest;
    public float $late_payment_interest_rate; // Pourcentage annuel
    
    // Paramètres d'escalade
    public bool $auto_escalate_to_litigation;
    public string $litigation_contact_email;

    public static function group(): string
    {
        return 'recovery';
    }

    public static function defaults(): array
    {
        return [
            'recovery_reminder1_days' => 15, // Premier rappel 15 jours après échéance
            'recovery_reminder2_days' => 30, // Deuxième rappel 30 jours après premier
            'recovery_mise_en_demeure_days' => 15, // Mise en demeure 15 jours après 2e rappel
            'recovery_litigation_threshold_amount' => 10000.00, // Seuil de 10 000 pour contentieux
            'mise_en_demeure_template_text' => "Madame, Monsieur,\n\nNous vous informons que malgré nos précédents rappels, votre créance d'un montant de [MONTANT] demeure impayée.\n\nVous disposez d'un délai de 30 jours à compter de la réception de cette mise en demeure pour régulariser votre situation.\n\nÀ défaut de paiement dans ce délai, nous nous verrons contraints d'engager une procédure de recouvrement judiciaire.\n\nCordialement,\nLe service de recouvrement",
            'enable_automatic_reminders' => true,
            'send_email_notifications' => true,
            'send_sms_notifications' => false,
            'enable_late_payment_interest' => true,
            'late_payment_interest_rate' => 12.0, // 12% par an
            'auto_escalate_to_litigation' => false,
            'litigation_contact_email' => 'contentieux@gouv.gn',
        ];
    }
}