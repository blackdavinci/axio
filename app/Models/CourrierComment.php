<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourrierComment extends Model
{
    protected $table = 'courrier_comments';
    
    protected $fillable = [
        'content',
        'commentable_id',
        'commentable_type',
        'user_id',
        'parent_id',
        'mentions',
        'edited_at',
    ];

    protected $casts = [
        'mentions' => 'array',
        'edited_at' => 'datetime',
    ];

    // Relations
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CourrierComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CourrierComment::class, 'parent_id')->orderBy('created_at');
    }

    public function allReplies(): HasMany
    {
        return $this->replies()->with('allReplies');
    }

    // Scopes
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithReplies($query)
    {
        return $query->with(['replies' => function ($query) {
            $query->with('user')->orderBy('created_at');
        }]);
    }

    // Accesseurs
    public function getIsEditedAttribute(): bool
    {
        return !is_null($this->edited_at);
    }

    public function getReplyCountAttribute(): int
    {
        return $this->replies()->count();
    }

    // Mutateurs
    public function markAsEdited(): void
    {
        $this->update(['edited_at' => now()]);
    }

    // Méthodes utilitaires
    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $this->user_id === $user->id || $user->hasRole('admin');
    }

    public function extractMentions(string $content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        return array_unique($matches[1]);
    }

    public function notifyMentionedUsers(): void
    {
        if (!$this->mentions) {
            return;
        }

        foreach ($this->mentions as $username) {
            $user = User::where('name', $username)->first();
            if ($user && $user->id !== $this->user_id) {
                // Ici vous pouvez ajouter la logique de notification
                // Par exemple avec une notification Laravel
            }
        }
    }
}
