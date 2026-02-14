<?php

namespace App\Enums;

enum PlanType: string
{
    case Membership = 'membership';
    case Trainer = 'trainer';
    case Senior = 'senior';
    case Student = 'student';
    case Comped = 'comped';

    public function label(): string
    {
        return match ($this) {
            self::Membership => 'Membership',
            self::Trainer => 'Registered Trainer',
            self::Senior => 'Senior',
            self::Student => 'Student',
            self::Comped => 'Comped',
        };
    }
}
