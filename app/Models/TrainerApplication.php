<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TrainerApplication extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'credentials',
        'experience_description',
        'license_number',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
        'stripe_payment_intent_id',
        'invoice_id',
        'amount_paid_cents',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('letter_of_nomination')->singleFile();
        $this->addMediaCollection('application_submission')->singleFile();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
