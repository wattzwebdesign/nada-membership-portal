<?php

namespace App\Models;

use App\Enums\FormFieldType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventFormField extends Model
{
    protected $fillable = [
        'event_id',
        'type',
        'label',
        'name',
        'placeholder',
        'help_text',
        'default_value',
        'is_required',
        'sort_order',
        'is_active',
        'conditional_on_field_id',
        'conditional_operator',
        'conditional_value',
        'conditional_on_package_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => FormFieldType::class,
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(EventFormFieldOption::class)->orderBy('sort_order');
    }

    public function conditionalField(): BelongsTo
    {
        return $this->belongsTo(self::class, 'conditional_on_field_id');
    }

    public function conditionalPackage(): BelongsTo
    {
        return $this->belongsTo(EventPricingPackage::class, 'conditional_on_package_id');
    }

    public function hasOptions(): bool
    {
        return $this->type->hasOptions();
    }

    public function isDisplayOnly(): bool
    {
        return $this->type->isDisplayOnly();
    }
}
