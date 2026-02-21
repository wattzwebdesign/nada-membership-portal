<?php

namespace App\Filament\Pages\EventReports\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;

class RegistrationsByEventChart extends ChartWidget
{
    protected static ?string $heading = 'Registrations by Event';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $events = Event::withCount(['registrations' => function ($q) {
            $q->where('status', '!=', 'canceled');
        }])
            ->orderBy('start_date', 'desc')
            ->take(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Registrations',
                    'data' => $events->pluck('registrations_count')->toArray(),
                    'backgroundColor' => '#1C3519',
                ],
            ],
            'labels' => $events->pluck('title')->map(fn ($t) => \Illuminate\Support\Str::limit($t, 20))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
