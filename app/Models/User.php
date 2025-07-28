<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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

class User extends Authenticatable implements Auditable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, AuditableTrait, LogsActivity, TwoFactorAuthenticatable, HasApiTokens;

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
        'actif',
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
            'actif' => 'boolean',
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


    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeDuService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
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
