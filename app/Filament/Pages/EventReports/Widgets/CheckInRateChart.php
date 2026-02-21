<?php

namespace App\Filament\Pages\EventReports\Widgets;

use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Widgets\ChartWidget;

class CheckInRateChart extends ChartWidget
{
    protected static ?string $heading = 'Check-In Rate by Event';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $events = Event::withCount([
            'registrations as total_registrations' => function ($q) {
                $q->where('status', '!=', 'canceled');
            },
            'registrations as checked_in_count' => function ($q) {
                $q->whereNotNull('checked_in_at');
            },
        ])
            ->where('start_date', '<', now())
            ->orderBy('start_date', 'desc')
            ->take(10)
            ->get();

        $rates = $events->map(function ($event) {
            return $event->total_registrations > 0
                ? round(($event->checked_in_count / $event->total_registrations) * 100, 1)
                : 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Check-In Rate (%)',
                    'data' => $rates->toArray(),
                    'backgroundColor' => '#2E522A',
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
