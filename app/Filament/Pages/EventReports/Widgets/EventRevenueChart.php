<?php

namespace App\Filament\Pages\EventReports\Widgets;

use App\Enums\EventPaymentStatus;
use App\Models\EventRegistration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EventRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Event Revenue (Last 6 Months)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $months = collect();
        $data = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->format('M Y'));

            $revenue = EventRegistration::where('payment_status', EventPaymentStatus::Paid->value)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount_cents');

            $data->push(round($revenue / 100, 2));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => $data->toArray(),
                    'borderColor' => '#AD7E07',
                    'backgroundColor' => 'rgba(173, 126, 7, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
