<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class NewMembersWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $newThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $newLastMonth = User::whereBetween('created_at', [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth(),
        ])->count();

        $description = $newLastMonth > 0
            ? ($newThisMonth >= $newLastMonth ? 'Up' : 'Down') . ' from ' . $newLastMonth . ' last month'
            : 'New this month';

        $color = $newThisMonth >= $newLastMonth ? 'success' : 'warning';

        return [
            Stat::make('New Members This Month', $newThisMonth)
                ->description($description)
                ->color($color)
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
