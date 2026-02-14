<?php

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ExpiringMembershipsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $expiringCount = Subscription::where('status', SubscriptionStatus::Active)
            ->whereBetween('current_period_end', [
                Carbon::now(),
                Carbon::now()->addDays(30),
            ])
            ->count();

        return [
            Stat::make('Expiring Within 30 Days', $expiringCount)
                ->description($expiringCount > 0 ? 'Memberships ending soon' : 'No upcoming expirations')
                ->color($expiringCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-clock'),
        ];
    }
}
