<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeAccount extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_connect_account_id',
        'onboarding_complete',
        'charges_enabled',
        'payouts_enabled',
        'default_currency',
        'details_submitted',
    ];

    protected function casts(): array
    {
        return [
            'onboarding_complete' => 'boolean',
            'charges_enabled' => 'boolean',
            'payouts_enabled' => 'boolean',
            'details_submitted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFullyOnboarded(): bool
    {
        return $this->onboarding_complete && $this->charges_enabled && $this->payouts_enabled;
    }
}
