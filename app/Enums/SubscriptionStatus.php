<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Incomplete = 'incomplete';
    case IncompleteExpired = 'incomplete_expired';
    case Trialing = 'trialing';
    case Unpaid = 'unpaid';
    case Paused = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Canceled => 'Canceled',
            self::Incomplete => 'Incomplete',
            self::IncompleteExpired => 'Incomplete Expired',
            self::Trialing => 'Trialing',
            self::Unpaid => 'Unpaid',
            self::Paused => 'Paused',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::PastDue => 'warning',
            self::Canceled => 'danger',
            self::Incomplete, self::IncompleteExpired => 'gray',
            self::Trialing => 'info',
            self::Unpaid => 'danger',
            self::Paused => 'warning',
        };
    }
}
