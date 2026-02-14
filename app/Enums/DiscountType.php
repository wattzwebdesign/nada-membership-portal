<?php

namespace App\Enums;

enum DiscountType: string
{
    case None = 'none';
    case Student = 'student';
    case Senior = 'senior';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::Student => 'Student',
            self::Senior => 'Senior',
        };
    }
}
