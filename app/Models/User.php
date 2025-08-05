<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements Auditable, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, AuditableTrait, LogsActivity, TwoFactorAuthenticatable, HasApiTokens;

    public function canAccessPanel(Panel $panel): bool
    {
        // Example condition: Allow access only to users with specific email domain and verified email

        // Check if the user has one of the allowed roles
        $allowedRoles = ['super_admin'];

        if ($this->hasAnyRole($allowedRoles) && $this->is_active) {
            return true;
        }

        return false;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'prenom',
        'nom',
        'genre',
        'photo',
        'avatar_url',
        'email',
        'password',
        'service_id',
        'structure_id',
        'telephone',
        'telephone_secondaire',
        'matricule',
        'grade',
        'adresse',
        'date_naissance',
        'categorie',
        'specialite',
        'personne_urgence',
        'telephone_urgence',
        'poste',
        'statut',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Example condition: Allow access only to users with specific email domain and verified email

        // Check if the user has one of the allowed roles
        $allowedRoles = ['super_admin', 'directeur', 'agent','secretaire','partenaire','chef','public'];

        if ($this->hasAnyRole($allowedRoles) && $this->statut) {
            return true;
        }

        return false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'statut' => 'boolean',
            'date_naissance' => 'date',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $prenom = Str::substr($this->prenom ?? '', 0, 1);
        $nom = Str::substr($this->nom ?? '', 0, 1);

        return $prenom . $nom;
    }

    /**
     * Get the user's full name
     */
    public function fullName(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }

    /**
     * Get the name attribute for Filament compatibility
     */
    public function getNameAttribute(): string
    {
        return $this->fullName();
    }

    /**
     * Get the avatar URL for Filament Breezy
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? \Storage::url($this->avatar_url) : null;
    }

    // Relations
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function courriersAssignes(): HasMany
    {
        return $this->hasMany(Courrier::class, 'user_id');
    }

    public function courriersCreated(): HasMany
    {
        return $this->hasMany(Courrier::class, 'created_by');
    }

    public function structuresChef(): HasMany
    {
        return $this->hasMany(Structure::class, 'chef_id');
    }


    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    // Accesseur pour is_active (utilise le champ 'actif' existant)
    public function getIsActiveAttribute(): bool
    {
        return $this->actif ?? true;
    }

    public function scopeDuService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeDeLaStructure($query, int $structureId)
    {
        return $query->where('structure_id', $structureId);
    }

    // Méthodes métier
    public function peutValiderCourriers(): bool
    {
        return $this->hasAnyRole(['super_admin', 'directeur', 'chef_service']);
    }

    public function peutAssignerTaches(): bool
    {
        return $this->hasAnyRole(['super_admin', 'directeur', 'chef_service']);
    }

    /**
     * Configuration pour Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
