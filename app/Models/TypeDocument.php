<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'couleur',
        'icone',
        'extensions_autorisees',
        'actif',
        'ordre_affichage',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'ordre_affichage' => 'integer',
        'extensions_autorisees' => 'array',
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

    // Méthodes utiles
    public function accepteExtension(string $extension): bool
    {
        if (empty($this->extensions_autorisees)) {
            return true; // Accepte toutes les extensions si non spécifié
        }
        
        return in_array(strtolower($extension), array_map('strtolower', $this->extensions_autorisees));
    }

    public function getExtensionsTexteAttribute(): string
    {
        return $this->extensions_autorisees ? implode(', ', $this->extensions_autorisees) : 'Toutes';
    }
}
