<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Priorite;

class PrioriteSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $priorites = [
            [
                'nom' => 'Très urgente',
                'code' => 'URGENTE',
                'description' => 'Traitement immédiat requis - délai critique',
                'couleur' => '#DC2626',
                'icone' => 'heroicon-o-fire',
                'delai_defaut' => 1,
                'ordre_affichage' => 1,
                'actif' => true,
            ],
            [
                'nom' => 'Haute',
                'code' => 'HAUTE',
                'description' => 'Priorité élevée - traitement rapide nécessaire',
                'couleur' => '#F59E0B',
                'icone' => 'heroicon-o-bolt',
                'delai_defaut' => 3,
                'ordre_affichage' => 2,
                'actif' => true,
            ],
            [
                'nom' => 'Normale',
                'code' => 'NORMALE',
                'description' => 'Traitement dans les délais standards',
                'couleur' => '#3B82F6',
                'icone' => 'heroicon-o-flag',
                'delai_defaut' => 7,
                'ordre_affichage' => 3,
                'actif' => true,
            ],
            [
                'nom' => 'Faible',
                'code' => 'FAIBLE',
                'description' => 'Peut être traité quand les ressources sont disponibles',
                'couleur' => '#10B981',
                'icone' => 'heroicon-o-arrow-down',
                'delai_defaut' => 15,
                'ordre_affichage' => 4,
                'actif' => true,
            ],
            [
                'nom' => 'Archive',
                'code' => 'ARCHIVE',
                'description' => 'Pour information - pas de traitement requis',
                'couleur' => '#6B7280',
                'icone' => 'heroicon-o-archive-box',
                'delai_defaut' => 30,
                'ordre_affichage' => 5,
                'actif' => true,
            ],
        ];

        foreach ($priorites as $priorite) {
            Priorite::create($priorite);
        }

        $this->command->info('Priorités créées avec succès !');
    }
}