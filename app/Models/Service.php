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

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class);
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

    // Accessors
    public function getCheminCompletAttribute(): string
    {
        $chemin = collect();
        $service = $this;
        $visited = collect(); // Protection contre les boucles infinies
        
        while ($service && !$visited->contains($service->id)) {
            $visited->push($service->id);
            $chemin->prepend($service->nom);
            $service = $service->parent;
            
            // Protection supplémentaire contre les boucles profondes
            if ($visited->count() > 10) {
                break;
            }
        }
        
        return $chemin->implode(' > ');
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
