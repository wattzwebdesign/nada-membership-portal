<?php

namespace App\Models;

use App\Enums\ClinicalLogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalLog extends Model
{
    protected $fillable = [
        'user_id',
        'trainer_id',
        'status',
        'completed_at',
        'reviewed_by',
        'reviewed_at',
        'reviewer_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClinicalLogStatus::class,
            'completed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ClinicalLogEntry::class);
    }

    // Computed

    public function getTotalHoursAttribute(): float
    {
        return (float) $this->entries()->sum('hours');
    }

    public function getHoursThresholdAttribute(): float
    {
        return (float) SiteSetting::get('clinical_hours_threshold', '40');
    }

    public function getProgressPercentAttribute(): float
    {
        $threshold = $this->hours_threshold;

        if ($threshold <= 0) {
            return 100;
        }

        return min(100, round(($this->total_hours / $threshold) * 100, 1));
    }

    public function getMeetsThresholdAttribute(): bool
    {
        return $this->total_hours >= $this->hours_threshold;
    }

    // Scopes

    public function scopeForTrainer(Builder $query, int $trainerId): Builder
    {
        return $query->where('trainer_id', $trainerId);
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', ClinicalLogStatus::Completed);
    }
}
