<?php

namespace App\Models;

use App\Enums\DiscountType;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName, MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'country',
        'stripe_customer_id',
        'discount_type',
        'discount_approved',
        'discount_approved_at',
        'discount_approved_by',
        'trainer_application_status',
        'trainer_approved_at',
        'trainer_approved_by',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'discount_type' => DiscountType::class,
            'discount_approved' => 'boolean',
            'discount_approved_at' => 'datetime',
            'trainer_approved_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Filament

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }

    // Relationships

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class, 'trainer_id');
    }

    public function trainingRegistrations(): HasMany
    {
        return $this->hasMany(TrainingRegistration::class);
    }

    public function clinicals(): HasMany
    {
        return $this->hasMany(Clinical::class);
    }

    public function discountRequests(): HasMany
    {
        return $this->hasMany(DiscountRequest::class);
    }

    public function stripeAccount(): HasOne
    {
        return $this->hasOne(StripeAccount::class);
    }

    public function payoutSetting(): HasOne
    {
        return $this->hasOne(PayoutSetting::class, 'trainer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function trainerApplications(): HasMany
    {
        return $this->hasMany(TrainerApplication::class);
    }

    // Scopes & Helpers

    public function isTrainer(): bool
    {
        return $this->hasRole('registered_trainer');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->where('status', 'active')->exists();
    }

    public function hasApprovedDiscount(): bool
    {
        return $this->discount_approved && $this->discount_type !== DiscountType::None;
    }

    public function hasConnectedStripeAccount(): bool
    {
        return $this->stripeAccount && $this->stripeAccount->isFullyOnboarded();
    }

    public function hasActiveTrainerPlan(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->whereHas('plan', fn ($q) => $q->where('role_required', 'registered_trainer'))
            ->exists();
    }

    public function canCreateTrainings(): bool
    {
        return $this->hasConnectedStripeAccount() && $this->hasActiveTrainerPlan();
    }
}
