<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'stripe_product_id',
        'stripe_price_id',
        'price_cents',
        'currency',
        'billing_interval',
        'billing_interval_count',
        'plan_type',
        'role_required',
        'discount_required',
        'is_visible',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'plan_type' => PlanType::class,
            'price_cents' => 'integer',
            'billing_interval_count' => 'integer',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getPriceFormattedAttribute(): string
    {
        return '$' . number_format($this->price_cents / 100, 2);
    }

    public function getBillingLabelAttribute(): string
    {
        $count = $this->billing_interval_count;
        $interval = $this->billing_interval;

        if ($count === 1) {
            return "per {$interval}";
        }

        return "every {$count} {$interval}s";
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true)->where('is_active', true);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->visible()
            ->where(function ($q) use ($user) {
                $q->whereNull('role_required')
                    ->orWhere(function ($q) use ($user) {
                        if ($user->isTrainer()) {
                            $q->where('role_required', 'registered_trainer');
                        } else {
                            $q->where('role_required', 'member');
                        }
                    });
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('discount_required')
                    ->orWhere(function ($q) use ($user) {
                        if ($user->hasApprovedDiscount()) {
                            $q->where('discount_required', $user->discount_type->value);
                        }
                    });
            });
    }
}
