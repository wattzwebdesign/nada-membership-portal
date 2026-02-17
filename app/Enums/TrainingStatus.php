<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TrainingStatus: string implements HasLabel, HasColor
{
    case PendingApproval = 'pending_approval';
    case Published = 'published';
    case Denied = 'denied';
    case Canceled = 'canceled';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingApproval => 'Pending Approval',
            self::Published => 'Published',
            self::Denied => 'Denied',
            self::Canceled => 'Canceled',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingApproval => 'warning',
            self::Published => 'success',
            self::Denied => 'danger',
            self::Canceled => 'danger',
            self::Completed => 'info',
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
