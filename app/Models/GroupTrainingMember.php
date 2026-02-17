<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupTrainingMember extends Model
{
    protected $fillable = [
        'group_training_request_id',
        'first_name',
        'last_name',
        'email',
    ];

    public function groupTrainingRequest(): BelongsTo
    {
        return $this->belongsTo(GroupTrainingRequest::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
