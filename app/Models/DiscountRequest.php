<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DiscountRequest extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'discount_type',
        'status',
        'proof_description',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'approval_token',
        'token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'reviewed_at' => 'datetime',
            'token_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isTokenValid(): bool
    {
        return $this->approval_token !== null
            && $this->token_expires_at !== null
            && $this->token_expires_at->isFuture()
            && $this->status === 'pending';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('proof_documents');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
