<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorOrderSplit extends Model
{
    protected $fillable = [
        'order_id',
        'vendor_profile_id',
        'subtotal_cents',
        'platform_percentage',
        'vendor_percentage',
        'platform_fee_cents',
        'vendor_payout_cents',
        'status',
        'stripe_transfer_id',
        'shipped_at',
        'delivered_at',
        'canceled_at',
        'tracking_number',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'platform_percentage' => 'decimal:2',
            'vendor_percentage' => 'decimal:2',
            'platform_fee_cents' => 'integer',
            'vendor_payout_cents' => 'integer',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function getVendorPayoutFormattedAttribute(): string
    {
        return '$' . number_format($this->vendor_payout_cents / 100, 2);
    }

    public function getPlatformFeeFormattedAttribute(): string
    {
        return '$' . number_format($this->platform_fee_cents / 100, 2);
    }
}
