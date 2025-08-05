<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Notifications\UserMentionedNotification;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Coolsam\NestedComments\Concerns\HasComments;

class Courrier extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity, HasComments;
    protected $fillable = [
        'numero_courrier',
        'reference',
        'objet',
        'contenu',
        'statut',
        'date_limite_traitement',
        'type_courrier_id',
        'priorite_id',
        'created_by',
        'service_id',
        'commentaires',
        'expediteur_id',
        'date_reception',
    ];

    protected $casts = [
        'date_limite_traitement' => 'datetime',
        'date_reception' => 'date',
        'mentions' => 'array',
        'expediteur_type' => 'string',
    ];

    // Collections de médias
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    // Relations
    public function typeCourrier(): BelongsTo
    {
        return $this->belongsTo(TypeCourrier::class);
    }

    public function priorite(): BelongsTo
    {
        return $this->belongsTo(Priorite::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Nouvelle relation pour les assignations
    public function assignments(): HasMany
    {
        return $this->hasMany(CourrierAssignment::class)->latest('assigned_at'); // Les plus récentes d'abord
    }

    // Accesseur pour obtenir la dernière (actuelle) assignation
    public function getCurrentAssignmentAttribute()
    {
        return $this->assignments->first();
    }

    // Accesseur pour obtenir le département (structure) assigné actuel
    public function getCurrentAssignedStructureAttribute()
    {
        return $this->currentAssignment?->structure;
    }

    // Accesseur pour obtenir l'utilisateur assigné actuel
    public function getCurrentAssignedUserAttribute()
    {
        return $this->currentAssignment?->user;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Expediteur::class);
    }

    // Accesseurs
    public function statutLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->statut) {
                'recu' => 'Reçu',
                'en_attente_assignation' => 'En attente d\'assignation',
                'affecte' => 'Affecté',
                'en_cours_traitement' => 'En cours de traitement',
                'traite' => 'Traité',
                'archive' => 'Archivé',
                'rejete' => 'Rejeté',
                default => $this->statut,
            }
        );
    }

    public function statutColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->statut) {
                'recu' => 'info',
                'en_attente_assignation' => 'warning',
                'affecte' => 'primary',
                'en_cours_traitement' => 'warning',
                'traite' => 'success',
                'archive' => 'gray',
                'rejete' => 'danger',
                default => 'primary',
            }
        );
    }

    // Méthodes utilitaires
    public function generateNumero(): string
    {
        // Utiliser les paramètres de configuration
        $settings = app(\App\Settings\CourrierSettings::class);
        $prefix = $settings->courrier_entrant_prefix ?? 'CE';
        $year = now()->year;
        $counter = static::whereYear('created_at', $year)->count() + 1;

        // Format: {prefix}-{year}-{counter:4}
        return $prefix . '-' . $year . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }

    public function calculateDateLimiteTraitement(): void
    {
        if ($this->priorite && $this->date_reception) {
            $this->date_limite_traitement = now()->parse($this->date_reception)
                ->addDays($this->priorite->delai_defaut);
        }
    }

    public function extractMentions(string $content): array
    {
        // Extraire les mentions @username du contenu
        preg_match_all('/@(\w+)/', $content, $matches);
        return array_unique($matches[1]);
    }

    public function notifyMentionedUsers(User $mentionedBy): void
    {
        if (!$this->mentions) {
            return;
        }

        foreach ($this->mentions as $email) {
            $user = User::where('email', $email)->first();
            if ($user && $user->id !== $mentionedBy->id) {
                $user->notify(new UserMentionedNotification($this, $mentionedBy));
            }
        }
    }

    // Boot method pour auto-générer le numéro et calculer la date limite
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($courrier) {
            // Générer le numéro automatiquement
            $courrier->numero_courrier = $courrier->generateNumero();

            // Définir priorité par défaut si pas définie
            if (!$courrier->priorite_id) {
                $prioriteNormale = \App\Models\Priorite::where('nom', 'Normale')->first();
                if ($prioriteNormale) {
                    $courrier->priorite_id = $prioriteNormale->id;
                }
            }

            // Calculer la date limite de traitement
            if ($courrier->priorite_id && !$courrier->date_limite_traitement) {
                $courrier->calculateDateLimiteTraitement();
            }
        });

        static::saving(function ($courrier) {
            // Recalculer la date limite si la priorité change
            if ($courrier->isDirty('priorite_id') || $courrier->isDirty('date_reception')) {
                $courrier->calculateDateLimiteTraitement();
            }
        });
    }

    /**
     * Configuration pour Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Courrier créé',
                'updated' => 'Courrier modifié',
                'deleted' => 'Courrier supprimé',
                default => "Courrier {$eventName}",
            });
    }

    /**
     * Méthodes pour le plugin nested-comments
     */
    public function getUserName($user): string
    {
        if (!$user) return 'Utilisateur supprimé';
        return $user->fullName() ?? $user->name ?? 'Utilisateur';
    }

    public function getUserAvatar($user): ?string
    {
        if (!$user) return null;

        // Si l'utilisateur a un avatar uploadé
        if (method_exists($user, 'getFilamentAvatarUrl') && $user->getFilamentAvatarUrl()) {
            return $user->getFilamentAvatarUrl();
        }

        // Vérifier si l'utilisateur a une photo de profil avec Media Library
        if (method_exists($user, 'getFirstMediaUrl')) {
            $avatarUrl = $user->getFirstMediaUrl('avatars');
            if ($avatarUrl) {
                return $avatarUrl;
            }
        }

        // Générer un avatar avec Gravatar basé sur l'email
        if ($user->email) {
            return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?d=mp&s=40';
        }

        // Utiliser les initiales comme fallback
        return null; // Le plugin générera automatiquement un avatar avec les initiales
    }
}
