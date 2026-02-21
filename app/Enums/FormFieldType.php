<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FormFieldType: string implements HasLabel
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Email = 'email';
    case Phone = 'phone';
    case Number = 'number';
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Date = 'date';
    case FileUpload = 'file_upload';
    case Heading = 'heading';
    case Paragraph = 'paragraph';
    case Hidden = 'hidden';
    case Country = 'country';
    case State = 'state';

    public function getLabel(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Textarea => 'Textarea',
            self::Email => 'Email',
            self::Phone => 'Phone',
            self::Number => 'Number',
            self::Select => 'Select',
            self::MultiSelect => 'Multi Select',
            self::Radio => 'Radio',
            self::Checkbox => 'Checkbox',
            self::Date => 'Date',
            self::FileUpload => 'File Upload',
            self::Heading => 'Heading',
            self::Paragraph => 'Paragraph',
            self::Hidden => 'Hidden',
            self::Country => 'Country',
            self::State => 'State',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::MultiSelect, self::Radio, self::Checkbox]);
    }

    public function isDisplayOnly(): bool
    {
        return in_array($this, [self::Heading, self::Paragraph]);
    }
}
