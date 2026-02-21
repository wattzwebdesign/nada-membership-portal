<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\PlanType;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'organization',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'country',
        'discount_type',
        'profile_photo_path',
        'nda_accepted_at',
        'latitude',
        'longitude',
        'bio',
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
            'nda_accepted_at' => 'datetime',
            'deleted_at' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
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
        return $this->hasOne(PayoutSetting::class, 'user_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function trainerApplications(): HasMany
    {
        return $this->hasMany(TrainerApplication::class);
    }

    public function agreementSignatures(): HasMany
    {
        return $this->hasMany(AgreementSignature::class);
    }

    public function trainerBroadcasts(): HasMany
    {
        return $this->hasMany(TrainerBroadcast::class, 'trainer_id');
    }

    public function bookmarkedResources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'resource_bookmarks')->withPivot('created_at');
    }

    public function walletPasses(): HasMany
    {
        return $this->hasMany(WalletPass::class);
    }

    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function vendorApplications(): HasMany
    {
        return $this->hasMany(VendorApplication::class);
    }

    public function shopOrders(): HasMany
    {
        return $this->hasMany(Order::class);
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

    public function isVendor(): bool
    {
        return $this->hasRole('vendor');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    public function isCustomerOnly(): bool
    {
        return $this->hasRole('customer')
            && !$this->hasAnyRole(['member', 'registered_trainer', 'vendor', 'admin']);
    }

    public function hasActiveVendorProfile(): bool
    {
        return $this->vendorProfile && $this->vendorProfile->is_active;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->where('status', 'active')->exists();
    }

    public function hasAssociatePlan(): bool
    {
        return once(fn () => $this->subscriptions()
            ->where('status', 'active')
            ->whereHas('plan', fn ($q) => $q->where('plan_type', PlanType::Associate))
            ->exists());
    }

    public function hasFullMembership(): bool
    {
        return $this->hasActiveSubscription() && !$this->hasAssociatePlan();
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

    public function hasSignedNda(): bool
    {
        return $this->nda_accepted_at !== null;
    }

    // Public Trainer Directory Scopes

    public function scopeTrainersPublic($query)
    {
        return $query->role('registered_trainer');
    }

    public function scopeTrainersWithLocation($query)
    {
        return $query->trainersPublic()->whereNotNull('latitude')->whereNotNull('longitude');
    }

    // Public Trainer Directory Accessors

    public function getLocationDisplayAttribute(): string
    {
        return collect([$this->city, $this->state])->filter()->implode(', ');
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return null;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(
            mb_substr($this->first_name ?? '', 0, 1) . mb_substr($this->last_name ?? '', 0, 1)
        );
    }

    public function getPhoneFormattedAttribute(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        // Strip leading 1 for US numbers
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6));
        }

        return $this->phone;
    }
}
