<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutFieldConfig extends Model
{
    protected $fillable = [
        'field_name',
        'label',
        'is_visible',
        'is_required',
        'sort_order',
        'section',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'is_required' => 'boolean',
        ];
    }

    public static function getVisibleFields(?string $section = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::where('is_visible', true)->orderBy('section')->orderBy('sort_order');

        if ($section) {
            $query->where('section', $section);
        }

        return $query->get();
    }

    public static function getValidationRules(): array
    {
        $rules = [];
        $fields = static::where('is_visible', true)->get();

        foreach ($fields as $field) {
            $fieldRules = [];

            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if (str_contains($field->field_name, 'email')) {
                $fieldRules[] = 'email';
            }

            $fieldRules[] = 'string';
            $fieldRules[] = 'max:255';

            $rules[$field->field_name] = $fieldRules;
        }

        return $rules;
    }
}
