<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CourrierSettings extends Settings
{
    public string $courrier_entrant_prefix;
    public string $courrier_sortant_prefix;
    public int $courrier_entrant_counter;
    public int $courrier_sortant_counter;
    public string $numero_format;
    public int $delai_traitement_standard;
    public int $delai_traitement_urgent;
    public int $delai_escalade;
    public bool $auto_attribution;
    public array $niveaux_priorite;

    public static function group(): string
    {
        return 'courrier';
    }

    public static function defaults(): array
    {
        return [
            'courrier_entrant_prefix' => 'CE',
            'courrier_sortant_prefix' => 'CS',
            'courrier_entrant_counter' => 1,
            'courrier_sortant_counter' => 1,
            'numero_format' => '{prefix}-{year}-{counter:4}',
            'delai_traitement_standard' => 7, // jours
            'delai_traitement_urgent' => 2, // jours
            'delai_escalade' => 5, // jours
            'auto_attribution' => true,
            'niveaux_priorite' => [
                'faible' => ['label' => 'Faible', 'color' => 'gray', 'delai' => 15],
                'normale' => ['label' => 'Normale', 'color' => 'blue', 'delai' => 7],
                'haute' => ['label' => 'Haute', 'color' => 'orange', 'delai' => 3],
                'urgente' => ['label' => 'Urgente', 'color' => 'red', 'delai' => 1],
            ],
        ];
    }
}