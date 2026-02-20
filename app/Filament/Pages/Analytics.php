<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetStatsGrouped;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetGraphPageViews;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetGraphSessions;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetTableUrls;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetTableReferrers;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetTableBrowser;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetTableOs;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetTableDevice;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetTableCountry;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetGraphEvents;
use Schmeits\FilamentUmami\Widgets\UmamiWidgetTableEvents;

class Analytics extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $title = 'Analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static string $routePath = '/analytics';

    protected static ?int $navigationSort = 98;

    public function getWidgets(): array
    {
        return [
            UmamiWidgetStatsGrouped::class,
            UmamiWidgetGraphPageViews::class,
            UmamiWidgetGraphSessions::class,
            UmamiWidgetGraphEvents::class,
            UmamiWidgetTableEvents::class,
            UmamiWidgetTableUrls::class,
            UmamiWidgetTableReferrers::class,
            UmamiWidgetTableBrowser::class,
            UmamiWidgetTableOs::class,
            UmamiWidgetTableDevice::class,
            UmamiWidgetTableCountry::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
