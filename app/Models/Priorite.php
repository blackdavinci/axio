<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Priorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'couleur',
        'icone',
        'delai_defaut',
        'actif',
        'ordre_affichage',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'delai_defaut' => 'integer',
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
            '#EF4444', '#DC2626' => 'danger',
            '#F59E0B', '#D97706' => 'warning', 
            '#10B981', '#059669' => 'success',
            '#3B82F6', '#2563EB' => 'primary',
            '#6B7280', '#4B5563' => 'gray',
            default => 'primary'
        };
    }

    // Relations (pour futur usage avec courriers)
    public function courriers()
    {
        return $this->hasMany(\App\Models\Courrier::class, 'priorite_id');
    }
}
