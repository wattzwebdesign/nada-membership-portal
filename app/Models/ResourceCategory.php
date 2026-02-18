<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResourceCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class);
    }

    public function publishedResources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class)->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
