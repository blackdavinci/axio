@component('mail::message')
# Réinitialisation de votre mot de passe

Bonjour {{ $user->name }},

Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte sur {{ $appName }}.

@component('mail::button', ['url' => $resetUrl, 'color' => 'primary'])
Réinitialiser le mot de passe
@endcomponent

**Ce lien expirera dans 60 minutes.**

Si vous n'avez pas demandé cette réinitialisation, aucune action n'est requise de votre part.

---

**Informations du compte :**
- Email : {{ $user->email }}
- Service : {{ $user->service ? $user->service->nom : 'Non assigné' }}
- Poste : {{ $user->poste ?? 'Non renseigné' }}

Pour votre sécurité, si vous pensez que cette demande n'est pas légitime, contactez immédiatement l'administrateur système.

Cordialement,<br>
L'équipe {{ $appName }}

@component('mail::subcopy')
Si vous avez des difficultés à cliquer sur le bouton "Réinitialiser le mot de passe", copiez et collez l'URL ci-dessous dans votre navigateur web :
{{ $resetUrl }}
@endcomponent
@endcomponent