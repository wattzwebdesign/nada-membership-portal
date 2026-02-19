<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Invoice;
use App\Models\Subscription;
use Filament\Support\RawJs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class AverageRevenueChart extends BaseReportChart
{
    protected static ?string $heading = 'Avg Revenue per Subscriber';

    protected static string $color = 'warning';

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

        $currentValues = $this->computeArpu($start, $end, $buckets);
        $prevValues = $this->computeArpu($prevStart, $prevEnd, $prevBuckets);
        $prevValues = $this->padArray($prevValues, count($currentValues));

        // Use cumulative ARPU for the description
        $currentArpu = $this->computePeriodArpu($start, $end);
        $prevArpu = $this->computePeriodArpu($prevStart, $prevEnd);

        $this->cachedDescription = $this->formatDescription($currentArpu, $prevArpu, '$');

        return [
            'datasets' => [
                $this->makeLineDataset('ARPU', $currentValues, '#f59e0b'),
                $this->makeComparisonDataset('Previous Period', $prevValues),
            ],
            'labels' => $buckets->pluck('label')->toArray(),
        ];
    }

    /**
     * Compute ARPU per bucket.
     */
    private function computeArpu(Carbon $start, Carbon $end, \Illuminate\Support\Collection $buckets): array
    {
        // Daily revenue
        $dailyRevenue = Invoice::select(
            DB::raw('DATE(paid_at) as date'),
            DB::raw('SUM(amount_paid) as total')
        )
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('total', 'date')
            ->map(fn ($v) => (float) $v);

        $revenueByBucket = $this->aggregateIntoBuckets($dailyRevenue, $buckets, 'sum');

        // Active subscriber count at end of each bucket
        return $buckets->values()->map(function ($bucket, $i) use ($revenueByBucket) {
            $activeCount = Subscription::where('created_at', '<=', $bucket['end'])
                ->where(function ($q) use ($bucket) {
                    $q->where('status', SubscriptionStatus::Active)
                        ->orWhere(function ($q2) use ($bucket) {
                            $q2->where('status', SubscriptionStatus::Canceled)
                                ->where('canceled_at', '>', $bucket['end']);
                        });
                })
                ->count();

            if ($activeCount === 0) {
                return 0;
            }

            return round($revenueByBucket[$i] / $activeCount, 2);
        })->toArray();
    }

    /**
     * Compute a single ARPU value for an entire period.
     */
    private function computePeriodArpu(Carbon $start, Carbon $end): float
    {
        $totalRevenue = Invoice::whereBetween('paid_at', [$start, $end])
            ->sum('amount_paid');

        $activeCount = Subscription::where('status', SubscriptionStatus::Active)->count();

        if ($activeCount === 0) {
            return 0;
        }

        return round((float) $totalRevenue / $activeCount, 2);
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
                            return '$' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
