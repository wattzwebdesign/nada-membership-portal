<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Invoice;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class RevenueChart extends BaseReportChart
{
    protected static ?string $heading = 'Revenue';

    protected static string $color = 'info';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getDateRange();
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange();
        $buckets = $this->generateBuckets($start, $end);
        $prevBuckets = $this->generateBuckets($prevStart, $prevEnd);

        // Current period revenue
        $dailyRevenue = Invoice::select(
            DB::raw('DATE(paid_at) as date'),
            DB::raw('SUM(amount_paid) as total')
        )
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('total', 'date')
            ->map(fn ($v) => (float) $v);

        $currentValues = $this->aggregateIntoBuckets($dailyRevenue, $buckets, 'sum');

        // Previous period revenue
        $prevDailyRevenue = Invoice::select(
            DB::raw('DATE(paid_at) as date'),
            DB::raw('SUM(amount_paid) as total')
        )
            ->whereBetween('paid_at', [$prevStart, $prevEnd])
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('total', 'date')
            ->map(fn ($v) => (float) $v);

        $prevValues = $this->aggregateIntoBuckets($prevDailyRevenue, $prevBuckets, 'sum');
        $prevValues = $this->padArray($prevValues, count($currentValues));

        $currentTotal = array_sum($currentValues);
        $prevTotal = array_sum($prevValues);

        $this->cachedDescription = $this->formatDescription($currentTotal, $prevTotal, '$');

        return [
            'datasets' => [
                $this->makeLineDataset('Revenue', $currentValues, '#8b5cf6'),
                $this->makeComparisonDataset('Previous Period', $prevValues),
            ],
            'labels' => $buckets->pluck('label')->toArray(),
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        }
                    },
                },
            },
            elements: {
                point: { radius: 2, hoverRadius: 4 },
                line: { tension: 0.3 },
            },
        }
        JS);
    }

    private string|HtmlString $cachedDescription = '';

    public function getDescription(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        if (empty($this->cachedDescription)) {
            $this->getData();
        }

        return $this->cachedDescription;
    }
}
