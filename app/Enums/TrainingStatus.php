<?php

namespace App\Enums;

enum TrainingStatus: string
{
    case PendingApproval = 'pending_approval';
    case Published = 'published';
    case Denied = 'denied';
    case Canceled = 'canceled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PendingApproval => 'Pending Approval',
            self::Published => 'Published',
            self::Denied => 'Denied',
            self::Canceled => 'Canceled',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingApproval => 'warning',
            self::Published => 'success',
            self::Denied => 'danger',
            self::Canceled => 'danger',
            self::Completed => 'info',
        };
    }
}
