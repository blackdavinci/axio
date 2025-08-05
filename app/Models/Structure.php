<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Structure extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'type',
        'description',
        'parent_id',
        'chef_id',
        'actif',
        'ordre',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Relations
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Structure::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(Structure::class, 'parent_id')->orderBy('ordre')->orderBy('nom');
    }

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class, 'structure_id'); // Relation avec la table users
    }

    // Scopes
    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('actif', true);
    }

    public function scopeDepartements(Builder $query): Builder
    {
        return $query->where('type', 'departement');
    }

    public function scopeServices(Builder $query): Builder
    {
        return $query->where('type', 'service');
    }

    public function scopeRacines(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeAvecParent(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeOrdonnes(Builder $query): Builder
    {
        return $query->orderBy('ordre')->orderBy('nom');
    }

    // Accesseurs
    public function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->type) {
                'departement' => 'Département',
                'service' => 'Service',
                default => $this->type,
            }
        );
    }

    public function cheminComplet(): Attribute
    {
        return Attribute::make(
            get: function () {
                $chemin = collect(['Direction générale']);
                $structure = $this;
                $visited = collect();

                // Construire le chemin en remontant la hiérarchie
                while ($structure && !$visited->contains($structure->id)) {
                    $visited->push($structure->id);
                    $chemin->push($structure->nom);
                    $structure = $structure->parent;

                    if ($visited->count() > 10) break; // Protection
                }

                return $chemin->implode(' > ');
            }
        );
    }

    public function estDepartement(): bool
    {
        return $this->type === 'departement';
    }

    public function estService(): bool
    {
        return $this->type === 'service';
    }

    public function estRattacheDirection(): bool
    {
        return $this->estService() && is_null($this->parent_id);
    }

    public function estRattacheDepartement(): bool
    {
        return $this->estService() && $this->parent?->estDepartement();
    }

    // Méthodes utilitaires
    public function getNombreEnfants(): int
    {
        return $this->enfants()->count();
    }

    public function getNombreUtilisateurs(): int
    {
        return $this->utilisateurs()->count();
    }

    public function getTotalUtilisateursRecursif(): int
    {
        $total = $this->getNombreUtilisateurs();
        
        foreach ($this->enfants as $enfant) {
            $total += $enfant->getTotalUtilisateursRecursif();
        }
        
        return $total;
    }

    // Génération automatique du code
    public static function genererCode(string $type): string
    {
        $prefix = match ($type) {
            'departement' => 'DEPT',
            'service' => 'SERV',
            default => 'STRUCT',
        };

        $count = static::where('type', $type)->count() + 1;
        return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($structure) {
            if (!$structure->code) {
                $structure->code = static::genererCode($structure->type);
            }
        });
    }
}
