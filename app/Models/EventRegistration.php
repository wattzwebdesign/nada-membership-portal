<?php

namespace App\Models;

use App\Enums\EventPaymentStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'registration_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'payment_status',
        'qr_code_token',
        'total_amount_cents',
        'is_member_pricing',
        'form_data',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'invoice_id',
        'checked_in_at',
        'checked_in_by',
        'notes',
        'canceled_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'payment_status' => EventPaymentStatus::class,
            'form_data' => 'array',
            'total_amount_cents' => 'integer',
            'is_member_pricing' => 'boolean',
            'checked_in_at' => 'datetime',
            'canceled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EventRegistration $registration) {
            if (empty($registration->qr_code_token)) {
                $registration->qr_code_token = (string) Str::uuid();
            }
            if (empty($registration->registration_number)) {
                $registration->registration_number = static::generateRegistrationNumber();
            }
        });
    }

    public static function generateRegistrationNumber(): string
    {
        $year = now()->format('Y');
        $latest = static::where('registration_number', 'like', "EVT-{$year}-%")
            ->orderByRaw("CAST(SUBSTRING(registration_number, " . (strlen("EVT-{$year}-") + 1) . ") AS UNSIGNED) DESC")
            ->value('registration_number');

        $next = $latest ? ((int) substr($latest, strlen("EVT-{$year}-"))) + 1 : 1;

        return "EVT-{$year}-" . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pricingPackages(): BelongsToMany
    {
        return $this->belongsToMany(EventPricingPackage::class, 'event_registration_pricing_package')
            ->withPivot('pricing_category_id', 'unit_price_cents', 'is_member_pricing', 'is_early_bird')
            ->withTimestamps();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function walletPasses(): HasMany
    {
        return $this->hasMany(WalletPass::class, 'event_registration_id');
    }

    // Helpers

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total_amount_cents / 100, 2);
    }

    public function isFree(): bool
    {
        return $this->total_amount_cents === 0;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === EventPaymentStatus::Paid
            || $this->payment_status === EventPaymentStatus::Comped;
    }
}
