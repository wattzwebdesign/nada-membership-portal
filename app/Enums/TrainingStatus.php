<?php

namespace App\Enums;

enum TrainingStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Canceled = 'canceled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Canceled => 'Canceled',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Canceled => 'danger',
            self::Completed => 'info',
        };
    }
}
