<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutSetting extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'platform_percentage',
        'payee_percentage',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'platform_percentage' => 'decimal:2',
            'payee_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->user();
    }

    public static function getForTrainer(?int $trainerId): self
    {
        return static::getForUser($trainerId, 'trainer');
    }

    public static function getForVendor(?int $userId): self
    {
        return static::getForUser($userId, 'vendor');
    }

    public static function getForUser(?int $userId, string $type = 'trainer'): self
    {
        if ($userId) {
            $custom = static::where('user_id', $userId)
                ->where('type', $type)
                ->where('is_active', true)
                ->first();

            if ($custom) {
                return $custom;
            }
        }

        return static::whereNull('user_id')->where('type', $type)->firstOrFail();
    }

    public static function globalDefault(string $type = 'trainer'): self
    {
        return static::whereNull('user_id')->where('type', $type)->firstOrFail();
    }
}
