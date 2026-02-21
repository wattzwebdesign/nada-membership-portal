<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventPricingPackage extends Model
{
    protected $fillable = [
        'event_pricing_category_id',
        'name',
        'description',
        'price_cents',
        'member_price_cents',
        'early_bird_price_cents',
        'early_bird_member_price_cents',
        'early_bird_deadline',
        'max_quantity',
        'quantity_sold',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'member_price_cents' => 'integer',
            'early_bird_price_cents' => 'integer',
            'early_bird_member_price_cents' => 'integer',
            'early_bird_deadline' => 'datetime',
            'max_quantity' => 'integer',
            'quantity_sold' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventPricingCategory::class, 'event_pricing_category_id');
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(EventRegistration::class, 'event_registration_pricing_package')
            ->withPivot('pricing_category_id', 'unit_price_cents', 'is_member_pricing', 'is_early_bird')
            ->withTimestamps();
    }

    public function getCurrentPrice(bool $isMember = false): int
    {
        $isEarlyBird = $this->early_bird_deadline && now()->lt($this->early_bird_deadline);

        if ($isEarlyBird && $isMember && $this->early_bird_member_price_cents !== null) {
            return $this->early_bird_member_price_cents;
        }

        if ($isEarlyBird && $this->early_bird_price_cents !== null) {
            return $this->early_bird_price_cents;
        }

        if ($isMember && $this->member_price_cents !== null) {
            return $this->member_price_cents;
        }

        return $this->price_cents;
    }

    public function isEarlyBird(): bool
    {
        return $this->early_bird_deadline && now()->lt($this->early_bird_deadline);
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->max_quantity !== null && $this->quantity_sold >= $this->max_quantity) {
            return false;
        }

        return true;
    }

    public function getPriceFormattedAttribute(): string
    {
        return '$' . number_format($this->price_cents / 100, 2);
    }

    public function getMemberPriceFormattedAttribute(): ?string
    {
        if ($this->member_price_cents === null) {
            return null;
        }

        return '$' . number_format($this->member_price_cents / 100, 2);
    }
}
