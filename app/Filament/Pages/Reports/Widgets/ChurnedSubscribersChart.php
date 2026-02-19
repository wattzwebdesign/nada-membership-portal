<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ChurnedSubscribersChart extends BaseReportChart
{
    protected static ?string $heading = 'Churned Subscribers';

    protected static string $color = 'danger';

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

        // Current period
        $dailyCanceled = Subscription::select(
            DB::raw('DATE(canceled_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('canceled_at', [$start, $end])
            ->groupBy(DB::raw('DATE(canceled_at)'))
            ->pluck('count', 'date');

        $currentValues = $this->aggregateIntoBuckets($dailyCanceled, $buckets, 'sum');

        // Previous period
        $prevDailyCanceled = Subscription::select(
            DB::raw('DATE(canceled_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('canceled_at', [$prevStart, $prevEnd])
            ->groupBy(DB::raw('DATE(canceled_at)'))
            ->pluck('count', 'date');

        $prevValues = $this->aggregateIntoBuckets($prevDailyCanceled, $prevBuckets, 'sum');
        $prevValues = $this->padArray($prevValues, count($currentValues));

        $currentTotal = array_sum($currentValues);
        $prevTotal = array_sum($prevValues);

        // Inverted: increase in churn = red
        $this->cachedDescription = $this->formatDescriptionInverted($currentTotal, $prevTotal);

        return [
            'datasets' => [
                $this->makeLineDataset('Churned Subscribers', $currentValues, '#f43f5e'),
                $this->makeComparisonDataset('Previous Period', $prevValues),
            ],
            'labels' => $buckets->pluck('label')->toArray(),
        ];
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
