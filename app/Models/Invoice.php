<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_invoice_id',
        'stripe_subscription_id',
        'number',
        'status',
        'amount_due_cents',
        'amount_paid_cents',
        'currency',
        'period_start',
        'period_end',
        'paid_at',
        'hosted_invoice_url',
        'invoice_pdf_url',
    ];

    protected function casts(): array
    {
        return [
            'amount_due_cents' => 'integer',
            'amount_paid_cents' => 'integer',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAmountDueFormattedAttribute(): string
    {
        return '$' . number_format($this->amount_due_cents / 100, 2);
    }

    public function getAmountPaidFormattedAttribute(): string
    {
        return '$' . number_format($this->amount_paid_cents / 100, 2);
    }
}
