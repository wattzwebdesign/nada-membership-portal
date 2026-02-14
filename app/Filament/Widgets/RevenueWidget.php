<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class RevenueWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $revenueThisMonth = Invoice::where('paid_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount_paid_cents');

        $revenueLastMonth = Invoice::whereBetween('paid_at', [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth(),
        ])->sum('amount_paid_cents');

        $formattedRevenue = '$' . number_format($revenueThisMonth / 100, 2);

        $description = $revenueLastMonth > 0
            ? ($revenueThisMonth >= $revenueLastMonth ? 'Up' : 'Down') . ' from $' . number_format($revenueLastMonth / 100, 2) . ' last month'
            : 'Revenue this month';

        $color = $revenueThisMonth >= $revenueLastMonth ? 'success' : 'warning';

        return [
            Stat::make('Revenue This Month', $formattedRevenue)
                ->description($description)
                ->color($color)
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
