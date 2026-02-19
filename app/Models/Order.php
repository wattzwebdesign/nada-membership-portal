<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'subtotal_cents',
        'shipping_cents',
        'tax_cents',
        'total_cents',
        'currency',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'shipping_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $last = static::withTrashed()->max('id') ?? 0;
                $order->order_number = 'NADA-ORD-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function vendorOrderSplits(): HasMany
    {
        return $this->hasMany(VendorOrderSplit::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', OrderStatus::Paid);
    }

    public function scopeStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status);
    }

    public function getCustomerFullNameAttribute(): string
    {
        return "{$this->customer_first_name} {$this->customer_last_name}";
    }

    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total_cents / 100, 2);
    }

    public function getSubtotalFormattedAttribute(): string
    {
        return '$' . number_format($this->subtotal_cents / 100, 2);
    }

    public function getShippingFormattedAttribute(): string
    {
        if ($this->shipping_cents === 0) {
            return 'Free';
        }

        return '$' . number_format($this->shipping_cents / 100, 2);
    }

    public function hasDigitalItems(): bool
    {
        return $this->items()->where('is_digital', true)->exists();
    }

    public function hasPhysicalItems(): bool
    {
        return $this->items()->where('is_digital', false)->exists();
    }
}
