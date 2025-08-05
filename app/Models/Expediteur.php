<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expediteur extends Model
{
    protected $fillable = [
        'nom',
        'type',
        'telephone',
        'email',
        'adresse',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Relations
    public function courriers(): HasMany
    {
        return $this->hasMany(Courrier::class);
    }

    // Accesseurs
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'personne' => 'Personne',
            'entreprise' => 'Entreprise',
            'administration' => 'Administration',
            default => $this->type,
        };
    }

    // Méthodes utilitaires
    public function getFullIdentification(): string
    {
        return $this->nom . ' (' . $this->getTypeLabel() . ')';
    }
}