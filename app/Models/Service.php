<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Service extends Model implements Auditable
{
    use AuditableTrait, LogsActivity;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'parent_id',
        'departement_id',
        'type_rattachement',
        'actif',
        'ordre',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'ordre' => 'integer',
    ];

    // Relations
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(Service::class, 'parent_id')->orderBy('ordre');
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Alias pour compatibilité
    public function users(): HasMany
    {
        return $this->utilisateurs();
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeRacines($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeRattachesDirection($query)
    {
        return $query->where('type_rattachement', 'direction');
    }

    public function scopeRattachesDepartement($query)
    {
        return $query->where('type_rattachement', 'departement');
    }

    public function scopeAvecDepartement($query)
    {
        return $query->whereNotNull('departement_id');
    }

    public function scopeSansDepartement($query)
    {
        return $query->whereNull('departement_id');
    }

    // Accessors
    public function getCheminCompletAttribute(): string
    {
        $chemin = collect();
        $service = $this;
        $visited = collect(); // Protection contre les boucles infinies
        
        // Ajouter Direction générale en racine
        $chemin->prepend('Direction générale');
        
        // Ajouter le département si le service y est rattaché
        if ($this->departement) {
            $chemin->push($this->departement->nom);
        }
        
        // Construire la hiérarchie des services
        while ($service && !$visited->contains($service->id)) {
            $visited->push($service->id);
            $chemin->push($service->nom);
            $service = $service->parent;
            
            // Protection supplémentaire contre les boucles profondes
            if ($visited->count() > 10) {
                break;
            }
        }
        
        return $chemin->implode(' > ');
    }

    public function getTypeRattachementLabelAttribute(): string
    {
        return match ($this->type_rattachement) {
            'direction' => 'Direction générale',
            'departement' => 'Département',
            default => 'Non défini',
        };
    }

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
