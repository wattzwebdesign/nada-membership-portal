<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class NewSubscribersChart extends BaseReportChart
{
    protected static ?string $heading = 'New Subscribers';

    protected static string $color = 'success';

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
        $dailyNew = Subscription::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date');

        $currentValues = $this->aggregateIntoBuckets($dailyNew, $buckets, 'sum');

        // Previous period
        $prevDailyNew = Subscription::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date');

        $prevValues = $this->aggregateIntoBuckets($prevDailyNew, $prevBuckets, 'sum');
        $prevValues = $this->padArray($prevValues, count($currentValues));

        $currentTotal = array_sum($currentValues);
        $prevTotal = array_sum($prevValues);

        $this->cachedDescription = $this->formatDescription($currentTotal, $prevTotal);

        return [
            'datasets' => [
                $this->makeLineDataset('New Subscribers', $currentValues, '#10b981'),
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
