<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'version',
        'is_active',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Relationships

    public function signatures(): HasMany
    {
        return $this->hasMany(AgreementSignature::class);
    }

    // Helpers

    public static function getActiveNda(): ?self
    {
        return static::active()->where('slug', 'nda')->first();
    }
}
