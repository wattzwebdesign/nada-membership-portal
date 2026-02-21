<?php

namespace App\Filament\Pages;

use App\Filament\Pages\EventReports\Widgets\CheckInRateChart;
use App\Filament\Pages\EventReports\Widgets\EventRevenueChart;
use App\Filament\Pages\EventReports\Widgets\EventStatsOverview;
use App\Filament\Pages\EventReports\Widgets\RegistrationsByEventChart;
use Filament\Pages\Page;

class EventReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Events';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Event Reports';

    protected static string $view = 'filament.pages.event-reports';

    protected function getHeaderWidgets(): array
    {
        return [
            EventStatsOverview::class,
            RegistrationsByEventChart::class,
            EventRevenueChart::class,
            CheckInRateChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
