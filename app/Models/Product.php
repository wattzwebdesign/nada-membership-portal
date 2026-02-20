<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected static function booted(): void
    {
        $clearCache = fn () => Cache::forget('chatbot:catalog-context');

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }

    protected $fillable = [
        'vendor_profile_id',
        'product_category_id',
        'title',
        'slug',
        'description',
        'sku',
        'price_cents',
        'member_price_cents',
        'shipping_fee_cents',
        'currency',
        'stock_quantity',
        'track_stock',
        'is_digital',
        'status',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'member_price_cents' => 'integer',
            'shipping_fee_cents' => 'integer',
            'stock_quantity' => 'integer',
            'track_stock' => 'boolean',
            'is_digital' => 'boolean',
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        $this->addMediaCollection('digital_file')->singleFile()
            ->acceptsMimeTypes(['application/pdf', 'application/zip', 'application/x-zip-compressed']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! SiteSetting::imageOptimizationEnabled()) {
            return;
        }

        $quality = SiteSetting::imageWebpQuality();
        $thumbSize = SiteSetting::imageThumbSize();

        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality($quality)
            ->nonQueued()
            ->performOnCollections('images');

        $this->addMediaConversion('thumb')
            ->width($thumbSize)
            ->height($thumbSize)
            ->format('webp')
            ->quality($quality)
            ->nonQueued()
            ->performOnCollections('images');
    }

    public function getEffectivePrice(?User $user = null): int
    {
        if ($user && $this->member_price_cents && $user->hasActiveSubscription()) {
            return $this->member_price_cents;
        }

        return $this->price_cents;
    }

    public function getShippingFeeCents(): int
    {
        if ($this->is_digital) {
            return 0;
        }

        if ($this->shipping_fee_cents !== null) {
            return $this->shipping_fee_cents;
        }

        return $this->vendorProfile->default_shipping_fee_cents ?? 0;
    }

    public function isInStock(): bool
    {
        if (! $this->track_stock) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    public function decrementStock(int $quantity = 1): void
    {
        if ($this->track_stock && $this->stock_quantity >= $quantity) {
            $this->decrement('stock_quantity', $quantity);
        }
    }

    public function isDigital(): bool
    {
        return $this->is_digital;
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeDigital($query)
    {
        return $query->where('is_digital', true);
    }

    public function scopePhysical($query)
    {
        return $query->where('is_digital', false);
    }

    // Accessors

    public function getPriceFormattedAttribute(): string
    {
        return '$' . number_format($this->price_cents / 100, 2);
    }

    public function getMemberPriceFormattedAttribute(): ?string
    {
        if (! $this->member_price_cents) {
            return null;
        }

        return '$' . number_format($this->member_price_cents / 100, 2);
    }

    public function getShippingFeeFormattedAttribute(): string
    {
        $fee = $this->getShippingFeeCents();

        if ($fee === 0) {
            return 'Free';
        }

        return '$' . number_format($fee / 100, 2);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('images');

        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion('thumb')
            ? $media->getUrl('thumb')
            : $media->getUrl();
    }
}
