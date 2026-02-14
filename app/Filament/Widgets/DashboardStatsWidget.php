<?php

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\DiscountRequest;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\TrainerApplication;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $activeMembers = Subscription::where('status', SubscriptionStatus::Active)->count();

        $newThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        $revenueThisMonth = Invoice::where('paid_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount_paid_cents');
        $formattedRevenue = '$' . number_format($revenueThisMonth / 100, 2);

        $pendingDiscounts = DiscountRequest::pending()->count();

        $pendingTrainerApps = TrainerApplication::pending()->count();

        $expiringCount = Subscription::where('status', SubscriptionStatus::Active)
            ->whereBetween('current_period_end', [
                Carbon::now(),
                Carbon::now()->addDays(30),
            ])
            ->count();

        return [
            Stat::make('Active Members', $activeMembers)
                ->description('Active subscriptions')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('New This Month', $newThisMonth)
                ->description('New registrations')
                ->color('primary')
                ->icon('heroicon-o-user-plus'),

            Stat::make('Revenue This Month', $formattedRevenue)
                ->description('From invoices')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Expiring in 30 Days', $expiringCount)
                ->description($expiringCount > 0 ? 'Memberships ending soon' : 'None expiring')
                ->color($expiringCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('Pending Discounts', $pendingDiscounts)
                ->description($pendingDiscounts > 0 ? 'Awaiting review' : 'All caught up')
                ->color($pendingDiscounts > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-receipt-percent'),

            Stat::make('Pending Trainer Apps', $pendingTrainerApps)
                ->description($pendingTrainerApps > 0 ? 'Awaiting review' : 'All caught up')
                ->color($pendingTrainerApps > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-academic-cap'),
        ];
    }
}
