<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use App\Mail\WelcomeUserMail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['actif'] = true; // Utilisateur actif par défaut
        
        // Générer un mot de passe automatique si aucun n'est fourni
        if (empty($data['password'])) {
            $generatedPassword = Str::random(12);
            $data['password'] = Hash::make($generatedPassword);
            
            // Stocker le mot de passe en clair pour l'email
            $this->generatedPassword = $generatedPassword;
            $this->shouldSendEmail = true;
        } else {
            $this->shouldSendEmail = false;
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        $role = $this->data['role'] ?? null;
        if ($role) {
            $this->record->syncRoles([$role]);
        }
        
        // Envoyer l'email de confirmation seulement si mot de passe généré
        if ($this->shouldSendEmail) {
            $this->sendWelcomeEmail();
        }
    }
    
    protected function sendWelcomeEmail(): void
    {
        try {
            $user = $this->record;
            $password = $this->generatedPassword;
            
            // Charger les relations nécessaires
            $user->load(['service', 'roles']);
            
            // Créer un email simple pour l'instant
            $emailData = [
                'prenom' => $user->prenom,
                'nom' => $user->nom,
                'email' => $user->email,
                'password' => $password,
                'login_url' => route('filament.admin.auth.login')
            ];
            
            // Envoyer l'email de bienvenue
            Mail::to($user->email, $user->fullName())
                ->send(new WelcomeUserMail($user, $password, $emailData['login_url']));
            
            // Notification sans mot de passe
            Notification::make()
                ->title('Compte créé avec succès')
                ->body("Utilisateur {$user->fullName()} créé. Un email de bienvenue a été envoyé.")
                ->success()
                ->send();
            
        } catch (\Exception $e) {
            // Log l'erreur pour diagnostic
            \Log::error('Erreur envoi email welcome', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
            Notification::make()
                ->title('Erreur envoi email')
                ->body('Le compte a été créé mais l\'email n\'a pas pu être envoyé. Erreur: ' . $e->getMessage())
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
