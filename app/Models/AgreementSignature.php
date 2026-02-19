<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgreementSignature extends Model
{
    protected $fillable = [
        'user_id',
        'agreement_id',
        'signed_at',
        'ip_address',
        'user_agent',
        'consent_context',
        'context_reference_type',
        'context_reference_id',
        'consent_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    // Scopes

    public function scopeTermsConsents(Builder $query): Builder
    {
        return $query->whereHas('agreement', fn (Builder $q) => $q->where('slug', 'terms-of-service'));
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}
