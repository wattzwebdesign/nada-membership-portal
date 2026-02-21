<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFormFieldOption extends Model
{
    protected $fillable = [
        'event_form_field_id',
        'label',
        'value',
        'price_adjustment_cents',
        'member_price_adjustment_cents',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment_cents' => 'integer',
            'member_price_adjustment_cents' => 'integer',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function formField(): BelongsTo
    {
        return $this->belongsTo(EventFormField::class, 'event_form_field_id');
    }
}
