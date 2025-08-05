<?php

namespace App\Services;

use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NimbaSmsService
{
    protected NotificationSettings $settings;

    public function __construct()
    {
        $this->settings = app(NotificationSettings::class);
    }

    /**
     * Envoyer un SMS via l'API NimbaSMS
     */
    public function sendSms(string $phoneNumber, string $message): array
    {
        try {
            // Nettoyer le numéro de téléphone
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            
            // Préparer la requête
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->settings->nimba_api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->settings->nimba_api_url . '/messages', [
                'from' => $this->settings->nimba_sender_id,
                'to' => $phoneNumber,
                'text' => $message,
                'test' => $this->settings->nimba_test_mode,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('SMS envoyé avec succès', [
                    'phone' => $phoneNumber,
                    'message_id' => $data['message_id'] ?? null,
                    'status' => $data['status'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message_id' => $data['message_id'] ?? null,
                    'status' => $data['status'] ?? 'sent',
                    'cost' => $data['cost'] ?? null,
                ];
            } else {
                Log::error('Erreur lors de l\'envoi SMS', [
                    'phone' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => $response->json()['message'] ?? 'Erreur inconnue',
                    'status_code' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi SMS', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envoyer des SMS en lot
     */
    public function sendBulkSms(array $recipients, string $message): array
    {
        $results = [];
        
        foreach ($recipients as $phoneNumber) {
            $results[] = array_merge(
                ['phone' => $phoneNumber],
                $this->sendSms($phoneNumber, $message)
            );
            
            // Petite pause pour éviter de surcharger l'API
            usleep(100000); // 0.1 seconde
        }

        return $results;
    }

    /**
     * Vérifier le solde du compte
     */
    public function getBalance(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->settings->nimba_api_key,
                'Accept' => 'application/json',
            ])->get($this->settings->nimba_api_url . '/balance');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'balance' => $response->json()['balance'] ?? 0,
                    'currency' => $response->json()['currency'] ?? 'GNF',
                ];
            }

            return [
                'success' => false,
                'error' => 'Impossible de récupérer le solde',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier le statut d'un message
     */
    public function getMessageStatus(string $messageId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->settings->nimba_api_key,
                'Accept' => 'application/json',
            ])->get($this->settings->nimba_api_url . '/messages/' . $messageId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'unknown',
                    'delivered_at' => $data['delivered_at'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Message non trouvé',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Tester la connexion à l'API
     */
    public function testConnection(): array
    {
        try {
            // Test avec un message factice
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->settings->nimba_api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->settings->nimba_api_url . '/messages', [
                'from' => $this->settings->nimba_sender_id,
                'to' => '+224000000000', // Numéro de test
                'text' => 'Test de connexion API',
                'test' => true, // Force le mode test
            ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'message' => $response->successful() 
                    ? 'Connexion API réussie' 
                    : 'Erreur de connexion: ' . ($response->json()['message'] ?? 'Erreur inconnue'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Formater le numéro de téléphone pour l'API
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Supprimer tous les espaces et caractères non numériques sauf +
        $phoneNumber = preg_replace('/[^+\d]/', '', $phoneNumber);
        
        // Si le numéro commence par 0, remplacer par +224 (Guinée)
        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '+224' . substr($phoneNumber, 1);
        }
        
        // Si le numéro ne commence pas par +, ajouter +224
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+224' . $phoneNumber;
        }
        
        return $phoneNumber;
    }

    /**
     * Vérifier si un numéro est valide pour la Guinée
     */
    public function isValidGuineanNumber(string $phoneNumber): bool
    {
        $formatted = $this->formatPhoneNumber($phoneNumber);
        
        // Numéro guinéen : +224 suivi de 8 ou 9 chiffres
        return preg_match('/^\+224[0-9]{8,9}$/', $formatted);
    }
}