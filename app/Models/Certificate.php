<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'certificate_code',
        'training_id',
        'issued_by',
        'date_issued',
        'expiration_date',
        'status',
        'pdf_path',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'date_issued' => 'date',
            'expiration_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->expiration_date === null || $this->expiration_date->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expiration_date && $this->expiration_date->isPast());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
