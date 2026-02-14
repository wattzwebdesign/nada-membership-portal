<?php

namespace App\Enums;

enum TrainingType: string
{
    case InPerson = 'in_person';
    case Virtual = 'virtual';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::InPerson => 'In Person',
            self::Virtual => 'Virtual',
            self::Hybrid => 'Hybrid',
        };
    }
}
