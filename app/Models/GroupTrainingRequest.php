<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupTrainingRequest extends Model
{
    protected $fillable = [
        'trainer_id',
        'company_first_name',
        'company_last_name',
        'company_email',
        'training_name',
        'training_date',
        'training_city',
        'training_state',
        'cost_per_ticket_cents',
        'number_of_tickets',
        'transaction_fee_cents',
        'total_amount_cents',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'training_date' => 'date',
            'cost_per_ticket_cents' => 'integer',
            'number_of_tickets' => 'integer',
            'transaction_fee_cents' => 'integer',
            'total_amount_cents' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupTrainingMember::class);
    }

    public function getCompanyFullNameAttribute(): string
    {
        return "{$this->company_first_name} {$this->company_last_name}";
    }

    public function training(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Training::class);
    }

    public function getSubtotalCentsAttribute(): int
    {
        return $this->cost_per_ticket_cents * $this->number_of_tickets;
    }

    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total_amount_cents / 100, 2);
    }
}
