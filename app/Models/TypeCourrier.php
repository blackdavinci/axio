<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeCourrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'couleur',
        'icone',
        'delai_traitement_defaut',
        'actif',
        'ordre_affichage',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'delai_traitement_defaut' => 'integer',
        'ordre_affichage' => 'integer',
    ];

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeOrdonnes($query)
    {
        return $query->orderBy('ordre_affichage')->orderBy('nom');
    }

    // Accesseurs
    public function getCouleurBadgeAttribute(): string
    {
        return match($this->couleur) {
            '#EF4444' => 'danger',
            '#F59E0B' => 'warning', 
            '#10B981' => 'success',
            '#3B82F6' => 'primary',
            '#6B7280' => 'gray',
            default => 'primary'
        };
    }
}
