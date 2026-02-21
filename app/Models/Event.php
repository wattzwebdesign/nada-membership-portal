<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'location_name',
        'location_address',
        'city',
        'state',
        'zip',
        'country',
        'latitude',
        'longitude',
        'virtual_link',
        'start_date',
        'end_date',
        'timezone',
        'registration_start_date',
        'registration_end_date',
        'max_attendees',
        'status',
        'featured_image_path',
        'is_featured',
        'contact_email',
        'contact_phone',
        'organizer_name',
        'confirmation_message',
        'confirmation_email_body',
        'created_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'registration_start_date' => 'datetime',
            'registration_end_date' => 'datetime',
            'published_at' => 'datetime',
            'status' => EventStatus::class,
            'is_featured' => 'boolean',
            'max_attendees' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
                $count = static::withTrashed()->where('slug', 'like', $event->slug . '%')->count();
                if ($count > 0) {
                    $event->slug .= '-' . ($count + 1);
                }
            }
        });
    }

    // Relationships

    public function pricingCategories(): HasMany
    {
        return $this->hasMany(EventPricingCategory::class)->orderBy('sort_order');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(EventFormField::class)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes

    public function scopePublished($query)
    {
        return $query->where('status', EventStatus::Published);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helpers

    public function isFull(): bool
    {
        if ($this->max_attendees === null) {
            return false;
        }

        return $this->registrations()
            ->whereNotIn('status', ['canceled'])
            ->count() >= $this->max_attendees;
    }

    public function spotsRemaining(): ?int
    {
        if ($this->max_attendees === null) {
            return null;
        }

        $registered = $this->registrations()
            ->whereNotIn('status', ['canceled'])
            ->count();

        return max(0, $this->max_attendees - $registered);
    }

    public function isRegistrationOpen(): bool
    {
        if ($this->status !== EventStatus::Published) {
            return false;
        }

        if ($this->isFull()) {
            return false;
        }

        $now = now();

        if ($this->registration_start_date && $now->lt($this->registration_start_date)) {
            return false;
        }

        if ($this->registration_end_date && $now->gt($this->registration_end_date)) {
            return false;
        }

        if ($now->gt($this->start_date)) {
            return false;
        }

        return true;
    }

    public function getLocationDisplayAttribute(): string
    {
        $parts = array_filter([
            $this->location_name,
            $this->city,
            $this->state,
        ]);

        return implode(', ', $parts) ?: 'TBD';
    }

    public function getDateDisplayAttribute(): string
    {
        if ($this->start_date->isSameDay($this->end_date)) {
            return $this->start_date->format('M j, Y g:i A') . ' - ' . $this->end_date->format('g:i A');
        }

        return $this->start_date->format('M j, Y') . ' - ' . $this->end_date->format('M j, Y');
    }

    public function getActiveRegistrationsCountAttribute(): int
    {
        return $this->registrations()->whereNotIn('status', ['canceled'])->count();
    }

    public function hasMemberPricing(): bool
    {
        return $this->pricingCategories()
            ->whereHas('packages', fn ($q) => $q->whereNotNull('member_price_cents'))
            ->exists();
    }
}
