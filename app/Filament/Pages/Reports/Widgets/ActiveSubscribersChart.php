<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActiveSubscribersChart extends BaseReportChart
{
    protected static ?string $heading = 'Active Subscribers';

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

        // Current period running total
        $currentData = $this->buildRunningTotal($start, $end);
        $currentValues = $this->aggregateIntoBuckets($currentData, $buckets, 'last');
        $currentValues = $this->fillNulls($currentValues);

        // Previous period running total
        $prevData = $this->buildRunningTotal($prevStart, $prevEnd);
        $prevValues = $this->aggregateIntoBuckets($prevData, $prevBuckets, 'last');
        $prevValues = $this->fillNulls($prevValues);
        $prevValues = $this->padArray($prevValues, count($currentValues));

        $currentEnd = end($currentValues) ?: 0;
        $prevEnd = end($prevValues) ?: 0;

        $this->cachedDescription = $this->formatDescription($currentEnd, $prevEnd);

        return [
            'datasets' => [
                $this->makeLineDataset('Active Subscribers', $currentValues, '#6366f1'),
                $this->makeComparisonDataset('Previous Period', $prevValues),
            ],
            'labels' => $buckets->pluck('label')->toArray(),
        ];
    }

    /**
     * Build a daily running total of active subscribers.
     */
    private function buildRunningTotal(Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        // Count of subscriptions active before the range started
        $startingCount = Subscription::where('created_at', '<', $start)
            ->where(function ($q) use ($start) {
                $q->where('status', SubscriptionStatus::Active)
                    ->orWhere(function ($q2) use ($start) {
                        $q2->where('status', SubscriptionStatus::Canceled)
                            ->where('canceled_at', '>=', $start);
                    });
            })
            ->count();

        // Daily new subscriptions in range
        $dailyNew = Subscription::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date');

        // Daily cancellations in range
        $dailyCanceled = Subscription::select(
            DB::raw('DATE(canceled_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('canceled_at', [$start, $end])
            ->groupBy(DB::raw('DATE(canceled_at)'))
            ->pluck('count', 'date');

        // Build running total day-by-day
        $running = collect();
        $total = $startingCount;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->format('Y-m-d');
            $total += ($dailyNew[$dateKey] ?? 0);
            $total -= ($dailyCanceled[$dateKey] ?? 0);
            $running[$dateKey] = max(0, $total);
            $cursor->addDay();
        }

        return $running;
    }

    private string|HtmlString $cachedDescription = '';

    public function getDescription(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        // Ensure getData() has run
        if (empty($this->cachedDescription)) {
            $this->getData();
        }

        return $this->cachedDescription;
    }
}
