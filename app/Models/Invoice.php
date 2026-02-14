<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_invoice_id',
        'stripe_subscription_id',
        'number',
        'status',
        'amount_due',
        'amount_paid',
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
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->number)) {
                $invoice->number = static::generateNextNumber();
            }
        });
    }

    public static function generateNextNumber(): string
    {
        $latest = static::where('number', 'like', 'NADA-%')
            ->orderByRaw("CAST(SUBSTRING(number, 6) AS UNSIGNED) DESC")
            ->value('number');

        $next = $latest ? ((int) substr($latest, 5)) + 1 : 1;

        return 'NADA-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getAmountDueFormattedAttribute(): string
    {
        return '$' . number_format($this->amount_due, 2);
    }

    public function getAmountPaidFormattedAttribute(): string
    {
        return '$' . number_format($this->amount_paid, 2);
    }

    public function recalculateTotal(): void
    {
        $this->update([
            'amount_due' => $this->items()->sum('total'),
        ]);
    }
}
