<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'vendor_profile_id',
        'product_title',
        'product_sku',
        'unit_price_cents',
        'quantity',
        'total_cents',
        'shipping_fee_cents',
        'was_member_price',
        'is_digital',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_cents' => 'integer',
            'quantity' => 'integer',
            'total_cents' => 'integer',
            'shipping_fee_cents' => 'integer',
            'was_member_price' => 'boolean',
            'is_digital' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total_cents / 100, 2);
    }

    public function getUnitPriceFormattedAttribute(): string
    {
        return '$' . number_format($this->unit_price_cents / 100, 2);
    }
}
