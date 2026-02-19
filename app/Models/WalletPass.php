<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletPass extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'pass_category',
        'training_registration_id',
        'serial_number',
        'pass_type_identifier',
        'google_object_id',
        'authentication_token',
        'last_updated_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_updated_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingRegistration(): BelongsTo
    {
        return $this->belongsTo(TrainingRegistration::class);
    }

    public function deviceRegistrations(): HasMany
    {
        return $this->hasMany(WalletDeviceRegistration::class);
    }

    public function scopeApple($query)
    {
        return $query->where('platform', 'apple');
    }

    public function scopeGoogle($query)
    {
        return $query->where('platform', 'google');
    }

    public function scopeMembership($query)
    {
        return $query->where('pass_category', 'membership');
    }

    public function scopeTraining($query)
    {
        return $query->where('pass_category', 'training');
    }
}
