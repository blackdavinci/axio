<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeCourrier;

class TypeCourrierSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Supprimer les anciens types s'ils existent
        TypeCourrier::truncate();

        $typesCourriers = [
            [
                'nom' => 'Lettre',
                'code' => 'LTR',
                'description' => 'Correspondance officielle et lettres administratives',
                'couleur' => '#3B82F6',
                'icone' => 'heroicon-o-envelope',
                'delai_traitement_defaut' => 7,
                'ordre_affichage' => 1,
                'actif' => true,
            ],
            [
                'nom' => 'Note de service',
                'code' => 'NDS',
                'description' => 'Instructions et communications internes',
                'couleur' => '#10B981',
                'icone' => 'heroicon-o-document-text',
                'delai_traitement_defaut' => 3,
                'ordre_affichage' => 2,
                'actif' => true,
            ],
            [
                'nom' => 'Décision',
                'code' => 'DEC',
                'description' => 'Décisions administratives et arrêtés',
                'couleur' => '#8B5CF6',
                'icone' => 'heroicon-o-scale',
                'delai_traitement_defaut' => 10,
                'ordre_affichage' => 3,
                'actif' => true,
            ],
            [
                'nom' => 'Demande',
                'code' => 'DMD',
                'description' => 'Demandes et requêtes diverses',
                'couleur' => '#F59E0B',
                'icone' => 'heroicon-o-hand-raised',
                'delai_traitement_defaut' => 5,
                'ordre_affichage' => 4,
                'actif' => true,
            ],
            [
                'nom' => 'Réclamation',
                'code' => 'RCL',
                'description' => 'Plaintes et réclamations',
                'couleur' => '#EF4444',
                'icone' => 'heroicon-o-exclamation-triangle',
                'delai_traitement_defaut' => 2,
                'ordre_affichage' => 5,
                'actif' => true,
            ],
            [
                'nom' => 'Ordre de mission',
                'code' => 'ODM',
                'description' => 'Ordres de mission et déplacements',
                'couleur' => '#06B6D4',
                'icone' => 'heroicon-o-map',
                'delai_traitement_defaut' => 1,
                'ordre_affichage' => 6,
                'actif' => true,
            ],
            [
                'nom' => 'Mise en demeure',
                'code' => 'MED',
                'description' => 'Mises en demeure et sommations',
                'couleur' => '#DC2626',
                'icone' => 'heroicon-o-shield-exclamation',
                'delai_traitement_defaut' => 1,
                'ordre_affichage' => 7,
                'actif' => true,
            ],
        ];

        foreach ($typesCourriers as $type) {
            TypeCourrier::create($type);
        }

        $this->command->info('Types de courriers créés avec succès !');
    }
}