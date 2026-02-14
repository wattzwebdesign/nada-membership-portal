<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutSetting extends Model
{
    protected $fillable = [
        'trainer_id',
        'platform_percentage',
        'trainer_percentage',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'platform_percentage' => 'decimal:2',
            'trainer_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public static function getForTrainer(?int $trainerId): self
    {
        if ($trainerId) {
            $custom = static::where('trainer_id', $trainerId)->where('is_active', true)->first();
            if ($custom) {
                return $custom;
            }
        }

        return static::whereNull('trainer_id')->firstOrFail();
    }

    public static function globalDefault(): self
    {
        return static::whereNull('trainer_id')->firstOrFail();
    }
}
