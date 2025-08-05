<?php

return [
    'mentionable' => [
        'model' => \App\Models\User::class,
        'column' => [
            'id' => 'id',
            'display_name' => 'nom', // Utiliser le champ nom de l'utilisateur
            'username' => 'email', // Utiliser l'email comme username
            'avatar' => 'avatar_url',
        ],
        'url' => 'admin/users/{id}',
        'lookup_key' => 'email',
        'search_key' => 'nom', // Rechercher par nom
    ],
    'default' => [
        'trigger_with' => [
            '@', // Seulement @ pour les mentions d'utilisateurs
        ],
        'trigger_configs' => [
            '@' => [
                'lookupKey' => 'email',
                'prefix' => '@',
                'suffix' => '',
                'titleField' => 'nom',
                'hintField' => 'fonction', // Afficher la fonction comme hint
            ],
        ],
        'menu_show_min_length' => 2,
        'menu_item_limit' => 10,
        'prefix' => '@',
        'suffix' => '',
        'title_field' => 'nom',
        'hint_field' => 'fonction',
    ],
];
