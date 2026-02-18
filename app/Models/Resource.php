<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Resource extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'excerpt',
        'is_members_only',
        'external_link',
        'video_embed',
        'is_published',
        'published_at',
        'wp_post_id',
    ];

    protected function casts(): array
    {
        return [
            'is_members_only' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ResourceCategory::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    public function canViewFullContent(): bool
    {
        if (! $this->is_members_only) {
            return true;
        }

        $user = auth()->user();

        return $user && $user->hasActiveSubscription();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
