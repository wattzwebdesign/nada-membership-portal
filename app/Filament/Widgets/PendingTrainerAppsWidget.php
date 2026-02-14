<?php

namespace App\Filament\Widgets;

use App\Models\TrainerApplication;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingTrainerAppsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $pendingCount = TrainerApplication::pending()->count();

        return [
            Stat::make('Pending Trainer Applications', $pendingCount)
                ->description($pendingCount > 0 ? 'Awaiting review' : 'All caught up')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-academic-cap'),
        ];
    }
}
