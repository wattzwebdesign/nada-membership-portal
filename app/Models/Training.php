<?php

namespace App\Models;

use App\Enums\TrainingStatus;
use App\Enums\TrainingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trainer_id',
        'title',
        'description',
        'type',
        'location_name',
        'location_address',
        'virtual_link',
        'start_date',
        'end_date',
        'timezone',
        'max_attendees',
        'is_paid',
        'price_cents',
        'currency',
        'stripe_price_id',
        'status',
        'is_group',
        'denied_reason',
    ];

    protected function casts(): array
    {
        return [
            'type' => TrainingType::class,
            'status' => TrainingStatus::class,
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'max_attendees' => 'integer',
            'is_paid' => 'boolean',
            'is_group' => 'boolean',
            'price_cents' => 'integer',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TrainingRegistration::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function invitees(): HasMany
    {
        return $this->hasMany(TrainingInvitee::class);
    }

    public function getPriceFormattedAttribute(): string
    {
        if (!$this->is_paid) {
            return 'Free';
        }
        return '$' . number_format($this->price_cents / 100, 2);
    }

    public function isFull(): bool
    {
        if ($this->max_attendees === null) {
            return false;
        }
        return $this->registrations()->whereNotIn('status', ['canceled'])->count() >= $this->max_attendees;
    }

    public function spotsRemaining(): ?int
    {
        if ($this->max_attendees === null) {
            return null;
        }
        return max(0, $this->max_attendees - $this->registrations()->whereNotIn('status', ['canceled'])->count());
    }

    public function scopePublished($query)
    {
        return $query->where('status', TrainingStatus::Published);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('status', TrainingStatus::Published)->where('is_group', false);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', TrainingStatus::PendingApproval);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }
}
