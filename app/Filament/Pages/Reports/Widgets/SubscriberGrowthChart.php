<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class SubscriberGrowthChart extends BaseReportChart
{
    protected static ?string $heading = 'Subscriber Growth';

    protected static string $color = 'info';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getDateRange();
        $buckets = $this->generateBuckets($start, $end);

        // Daily new subscriptions
        $dailyNew = Subscription::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date');

        // Daily cancellations
        $dailyCanceled = Subscription::select(
            DB::raw('DATE(canceled_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('canceled_at', [$start, $end])
            ->groupBy(DB::raw('DATE(canceled_at)'))
            ->pluck('count', 'date');

        $newValues = $this->aggregateIntoBuckets($dailyNew, $buckets, 'sum');
        $canceledValues = $this->aggregateIntoBuckets($dailyCanceled, $buckets, 'sum');

        // Net growth per bucket
        $netValues = [];
        $colors = [];

        for ($i = 0; $i < count($newValues); $i++) {
            $net = $newValues[$i] - $canceledValues[$i];
            $netValues[] = $net;
            $colors[] = $net >= 0 ? '#6366f1' : '#f59e0b';
        }

        $netTotal = array_sum($netValues);

        // Comparison with previous period
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange();

        $prevNew = Subscription::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevCanceled = Subscription::whereBetween('canceled_at', [$prevStart, $prevEnd])->count();
        $prevNetTotal = $prevNew - $prevCanceled;

        $this->cachedDescription = $this->formatDescription($netTotal, $prevNetTotal, '', ' net');

        return [
            'datasets' => [
                $this->makeBarDataset('Net Growth', $netValues, $colors),
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
