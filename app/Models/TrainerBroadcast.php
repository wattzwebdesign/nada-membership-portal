<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrainerBroadcast extends Model
{
    protected $fillable = [
        'trainer_id',
        'subject',
        'body',
        'recipient_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'recipient_count' => 'integer',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'trainer_broadcast_training');
    }
}
