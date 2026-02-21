<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EventPaymentStatus: string implements HasLabel, HasColor
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Comped = 'comped';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
            self::Refunded => 'Refunded',
            self::Comped => 'Comped',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::Refunded => 'danger',
            self::Comped => 'info',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function color(): string
    {
        return $this->getColor();
    }
}
