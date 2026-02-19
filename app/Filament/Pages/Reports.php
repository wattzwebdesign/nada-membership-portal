<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Reports\Widgets\ActiveSubscribersChart;
use App\Filament\Pages\Reports\Widgets\AverageRevenueChart;
use App\Filament\Pages\Reports\Widgets\ChurnedSubscribersChart;
use App\Filament\Pages\Reports\Widgets\NewSubscribersChart;
use App\Filament\Pages\Reports\Widgets\RevenueChart;
use App\Filament\Pages\Reports\Widgets\SubscriberGrowthChart;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Reports extends Dashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $title = 'Reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static string $routePath = '/reports';

    protected static ?int $navigationSort = 99;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('period')
                            ->label('Period')
                            ->options([
                                'today' => 'Today',
                                'last_7_days' => 'Last 7 days',
                                'last_4_weeks' => 'Last 4 weeks',
                                'last_3_months' => 'Last 3 months',
                                'last_12_months' => 'Last 12 months',
                                'month_to_date' => 'Month to date',
                                'quarter_to_date' => 'Quarter to date',
                                'year_to_date' => 'Year to date',
                                'all_time' => 'All time',
                                'custom' => 'Custom',
                            ])
                            ->default('last_4_weeks')
                            ->live(),
                        DatePicker::make('startDate')
                            ->label('Start date')
                            ->visible(fn (Get $get) => $get('period') === 'custom'),
                        DatePicker::make('endDate')
                            ->label('End date')
                            ->visible(fn (Get $get) => $get('period') === 'custom'),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            ActiveSubscribersChart::class,
            SubscriberGrowthChart::class,
            NewSubscribersChart::class,
            ChurnedSubscribersChart::class,
            RevenueChart::class,
            AverageRevenueChart::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
