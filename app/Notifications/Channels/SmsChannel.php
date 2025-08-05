<?php

namespace App\Notifications\Channels;

use App\Services\NimbaSmsService;
use App\Settings\NotificationSettings;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    protected NimbaSmsService $smsService;
    protected NotificationSettings $settings;

    public function __construct()
    {
        $this->smsService = app(NimbaSmsService::class);
        $this->settings = app(NotificationSettings::class);
    }

    /**
     * Envoyer la notification SMS.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$this->settings->enable_sms_notifications) {
            return;
        }

        // Obtenir les données SMS de la notification
        $smsData = $notification->toSms($notifiable);

        if (!$smsData || !isset($smsData['message'])) {
            return;
        }

        // Obtenir le numéro de téléphone
        $phoneNumber = $this->getPhoneNumber($notifiable, $smsData);
        
        if (!$phoneNumber) {
            return;
        }

        // Vérifier les limites quotidiennes
        if (!$this->canSendSms($notifiable)) {
            return;
        }

        // Ajouter la signature
        $message = $smsData['message'];
        if ($this->settings->notification_signature) {
            $message .= "\n\n" . $this->settings->notification_signature;
        }

        // Envoyer le SMS
        $result = $this->smsService->sendSms($phoneNumber, $message);

        // Enregistrer l'envoi pour les limites quotidiennes
        if ($result['success']) {
            $this->recordSmsSent($notifiable);
        }

        return $result;
    }

    /**
     * Obtenir le numéro de téléphone du destinataire
     */
    protected function getPhoneNumber($notifiable, array $smsData): ?string
    {
        // Essayer d'abord le numéro spécifié dans les données SMS
        if (isset($smsData['phone'])) {
            return $smsData['phone'];
        }

        // Ensuite essayer l'attribut telephone de l'utilisateur
        if (isset($notifiable->telephone)) {
            return $notifiable->telephone;
        }

        // Enfin essayer l'attribut phone
        if (isset($notifiable->phone)) {
            return $notifiable->phone;
        }

        return null;
    }

    /**
     * Vérifier si l'utilisateur peut recevoir un SMS (limite quotidienne)
     */
    protected function canSendSms($notifiable): bool
    {
        $cacheKey = 'sms_count_' . $notifiable->id . '_' . now()->format('Y-m-d');
        $sentToday = cache()->get($cacheKey, 0);

        return $sentToday < $this->settings->max_sms_per_day_per_user;
    }

    /**
     * Enregistrer l'envoi d'un SMS pour les compteurs
     */
    protected function recordSmsSent($notifiable): void
    {
        $cacheKey = 'sms_count_' . $notifiable->id . '_' . now()->format('Y-m-d');
        $sentToday = cache()->get($cacheKey, 0);
        
        cache()->put($cacheKey, $sentToday + 1, now()->endOfDay());
    }
}