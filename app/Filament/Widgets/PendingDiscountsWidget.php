<?php

namespace App\Filament\Widgets;

use App\Models\DiscountRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingDiscountsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $pendingCount = DiscountRequest::pending()->count();

        $stat = Stat::make('Pending Discount Requests', $pendingCount)
            ->description($pendingCount > 0 ? 'Awaiting review' : 'All caught up')
            ->color($pendingCount > 0 ? 'warning' : 'success')
            ->icon('heroicon-o-receipt-percent');

        // Link to Filament resource if it exists, otherwise use a generic admin URL
        if (class_exists(\App\Filament\Resources\DiscountRequestResource::class)) {
            $stat->url(\App\Filament\Resources\DiscountRequestResource::getUrl('index', ['tableFilters[status][value]' => 'pending']));
        }

        return [$stat];
    }
}
