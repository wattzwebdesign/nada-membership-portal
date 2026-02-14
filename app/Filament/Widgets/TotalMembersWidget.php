<?php

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalMembersWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Active Members', Subscription::where('status', SubscriptionStatus::Active)->count())
                ->description('Active subscriptions')
                ->color('success')
                ->icon('heroicon-o-users'),
        ];
    }
}
