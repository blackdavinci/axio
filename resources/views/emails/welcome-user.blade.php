<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .credentials {
            background-color: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196f3;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bienvenue dans notre système</h1>
    </div>
    
    <div class="content">
        <h2>Bonjour {{ $user->prenom }} {{ $user->nom }},</h2>
        
        <p>Votre compte utilisateur a été créé avec succès dans notre système d'administration.</p>
        
        <div class="credentials">
            <h3>Vos identifiants de connexion :</h3>
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Mot de passe temporaire :</strong> {{ $password }}</p>
        </div>
        
        <p><strong>Important :</strong> Pour des raisons de sécurité, nous vous recommandons fortement de changer votre mot de passe lors de votre première connexion.</p>
        
        <a href="{{ $loginUrl }}" class="button">Se connecter</a>
        
        <h3>Informations de votre compte :</h3>
        <ul>
            <li><strong>Service :</strong> {{ $user->service?->nom ?? 'Non assigné' }}</li>
            <li><strong>Rôle :</strong> {{ $user->roles?->first()?->name ?? 'Non assigné' }}</li>
            @if($user->poste)
                <li><strong>Poste :</strong> {{ $user->poste }}</li>
            @endif
        </ul>
        
        <p>Si vous avez des questions ou rencontrez des difficultés pour vous connecter, n'hésitez pas à contacter l'administrateur système.</p>
        
        <p>Cordialement,<br>L'équipe d'administration</p>
    </div>
    
    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
    </div>
</body>
</html>