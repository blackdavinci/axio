<?php

namespace App\Models;

use Coolsam\NestedComments\Concerns\HasComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CourrierAssignment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, HasComments;

    protected $fillable = [
        'courrier_id',
        'structure_id',
        'user_id',
        'notes',
        'assigned_at',
        'assigned_by_user_id',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    // Relations
    public function courrier(): BelongsTo
    {
        return $this->belongsTo(Courrier::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    // Spatie Media Library : Collection pour les pièces jointes à l'assignation
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('assignment_attachments');
    }

    // Spatie Activitylog options
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('courrier_assignment_activity')
            ->setDescriptionForEvent(fn(string $eventName) => "L'assignation a été {$eventName}");
    }
}
