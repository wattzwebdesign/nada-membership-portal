<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlossaryCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(GlossaryTerm::class);
    }

    public function publishedTerms(): HasMany
    {
        return $this->hasMany(GlossaryTerm::class)->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
