<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Registered = 'registered';
    case Attended = 'attended';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registered',
            self::Attended => 'Attended',
            self::Completed => 'Completed',
            self::NoShow => 'No Show',
            self::Canceled => 'Canceled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Registered => 'info',
            self::Attended => 'warning',
            self::Completed => 'success',
            self::NoShow => 'danger',
            self::Canceled => 'gray',
        };
    }
}
