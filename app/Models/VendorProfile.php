<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VendorProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'description',
        'email',
        'phone',
        'website',
        'default_shipping_fee_cents',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_shipping_fee_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function vendorOrderSplits(): HasMany
    {
        return $this->hasMany(VendorOrderSplit::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('gallery');
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
            ->performOnCollections('logo', 'gallery');

        $this->addMediaConversion('thumb')
            ->width($thumbSize)
            ->height($thumbSize)
            ->format('webp')
            ->quality($quality)
            ->nonQueued()
            ->performOnCollections('gallery');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDefaultShippingFeeFormattedAttribute(): string
    {
        return '$' . number_format($this->default_shipping_fee_cents / 100, 2);
    }

    public function getLogoUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('logo');

        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion('webp')
            ? $media->getUrl('webp')
            : $media->getUrl();
    }
}
