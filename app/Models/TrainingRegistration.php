<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingRegistration extends Model
{
    protected $fillable = [
        'training_id',
        'user_id',
        'status',
        'completed_at',
        'marked_complete_by',
        'stripe_payment_intent_id',
        'amount_paid_cents',
        'certificate_id',
        'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'completed_at' => 'datetime',
            'amount_paid_cents' => 'integer',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedCompleteBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_complete_by');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function walletPasses(): HasMany
    {
        return $this->hasMany(WalletPass::class);
    }
}
