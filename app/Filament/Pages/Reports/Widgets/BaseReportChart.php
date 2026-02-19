<?php

namespace App\Filament\Pages\Reports\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Support\RawJs;

abstract class BaseReportChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    /**
     * Resolve the current date range from filters.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function getDateRange(): array
    {
        $period = $this->filters['period'] ?? 'last_4_weeks';

        $end = Carbon::today()->endOfDay();

        $start = match ($period) {
            'today' => Carbon::today(),
            'last_7_days' => Carbon::today()->subDays(6),
            'last_4_weeks' => Carbon::today()->subWeeks(4)->addDay(),
            'last_3_months' => Carbon::today()->subMonths(3)->addDay(),
            'last_12_months' => Carbon::today()->subMonths(12)->addDay(),
            'month_to_date' => Carbon::today()->startOfMonth(),
            'quarter_to_date' => Carbon::today()->firstOfQuarter(),
            'year_to_date' => Carbon::today()->startOfYear(),
            'all_time' => Carbon::create(2020, 1, 1),
            'custom' => Carbon::parse($this->filters['startDate'] ?? Carbon::today()->subWeeks(4)->addDay()),
            default => Carbon::today()->subWeeks(4)->addDay(),
        };

        if ($period === 'custom' && ! empty($this->filters['endDate'])) {
            $end = Carbon::parse($this->filters['endDate'])->endOfDay();
        }

        return [$start->startOfDay(), $end];
    }

    /**
     * Get the previous period of equal length, ending the day before current start.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function getPreviousPeriodRange(): array
    {
        [$start, $end] = $this->getDateRange();

        $days = $start->diffInDays($end);
        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($days)->startOfDay();

        return [$prevStart, $prevEnd];
    }

    /**
     * Auto-select granularity based on range length.
     */
    protected function getGranularity(): string
    {
        [$start, $end] = $this->getDateRange();
        $days = $start->diffInDays($end);

        if ($days <= 31) {
            return 'daily';
        }

        if ($days <= 90) {
            return 'weekly';
        }

        return 'monthly';
    }

    /**
     * Generate time buckets for the x-axis.
     *
     * @return Collection<int, array{key: string, label: string, start: Carbon, end: Carbon}>
     */
    protected function generateBuckets(Carbon $start, Carbon $end): Collection
    {
        $granularity = $this->getGranularity();
        $buckets = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $bucketStart = $cursor->copy();

            $bucketEnd = match ($granularity) {
                'daily' => $cursor->copy()->endOfDay(),
                'weekly' => min($cursor->copy()->endOfWeek(Carbon::SUNDAY), $end->copy())->endOfDay(),
                'monthly' => min($cursor->copy()->endOfMonth(), $end->copy())->endOfDay(),
            };

            if ($bucketEnd->gt($end)) {
                $bucketEnd = $end->copy()->endOfDay();
            }

            $label = match ($granularity) {
                'daily' => $bucketStart->format('M j'),
                'weekly' => $bucketStart->format('M j'),
                'monthly' => $bucketStart->format('M Y'),
            };

            $buckets->push([
                'key' => $bucketStart->format('Y-m-d'),
                'label' => $label,
                'start' => $bucketStart,
                'end' => $bucketEnd,
            ]);

            $cursor = match ($granularity) {
                'daily' => $cursor->addDay()->startOfDay(),
                'weekly' => $cursor->next(Carbon::MONDAY)->startOfDay(),
                'monthly' => $cursor->addMonth()->startOfMonth(),
            };
        }

        return $buckets;
    }

    /**
     * Aggregate daily data into buckets using sum or last-value mode.
     *
     * @param  Collection<string, float|int>  $dailyData  Keyed by Y-m-d
     * @param  Collection  $buckets
     * @param  string  $mode  'sum' or 'last'
     * @return array<int, float|int>
     */
    protected function aggregateIntoBuckets(Collection $dailyData, Collection $buckets, string $mode = 'sum'): array
    {
        return $buckets->map(function ($bucket) use ($dailyData, $mode) {
            $bucketStart = $bucket['start'];
            $bucketEnd = $bucket['end'];

            $relevant = $dailyData->filter(function ($value, $date) use ($bucketStart, $bucketEnd) {
                $d = Carbon::parse($date);

                return $d->gte($bucketStart->startOfDay()) && $d->lte($bucketEnd->endOfDay());
            });

            if ($mode === 'last') {
                return $relevant->isEmpty() ? null : $relevant->last();
            }

            return $relevant->sum();
        })->toArray();
    }

    /**
     * Format the description with a big bold number and % change.
     */
    protected function formatDescription(float $current, float $previous, string $prefix = '', string $suffix = ''): string|HtmlString
    {
        $formatted = $prefix . number_format($current, ($current == (int) $current && $prefix !== '$') ? 0 : 2) . $suffix;

        if ($previous == 0 && $current == 0) {
            return new HtmlString(
                "<span class='text-xl font-bold text-gray-900 dark:text-white'>{$formatted}</span> "
                . "<span class='text-sm text-gray-500'>No change</span>"
            );
        }

        if ($previous == 0) {
            return new HtmlString(
                "<span class='text-xl font-bold text-gray-900 dark:text-white'>{$formatted}</span> "
                . "<span class='text-sm text-emerald-600'>New</span>"
            );
        }

        $change = (($current - $previous) / $previous) * 100;
        $arrow = $change >= 0 ? '↑' : '↓';
        $color = $change >= 0 ? 'text-emerald-600' : 'text-rose-600';
        $pct = number_format(abs($change), 1) . '%';

        return new HtmlString(
            "<span class='text-xl font-bold text-gray-900 dark:text-white'>{$formatted}</span> "
            . "<span class='text-sm {$color}'>{$arrow} {$pct} vs prior period</span>"
        );
    }

    /**
     * Format description where an increase is bad (churn).
     */
    protected function formatDescriptionInverted(float $current, float $previous, string $prefix = '', string $suffix = ''): string|HtmlString
    {
        $formatted = $prefix . number_format($current, ($current == (int) $current && $prefix !== '$') ? 0 : 2) . $suffix;

        if ($previous == 0 && $current == 0) {
            return new HtmlString(
                "<span class='text-xl font-bold text-gray-900 dark:text-white'>{$formatted}</span> "
                . "<span class='text-sm text-gray-500'>No change</span>"
            );
        }

        if ($previous == 0) {
            return new HtmlString(
                "<span class='text-xl font-bold text-gray-900 dark:text-white'>{$formatted}</span> "
                . "<span class='text-sm text-rose-600'>New</span>"
            );
        }

        $change = (($current - $previous) / $previous) * 100;
        $arrow = $change >= 0 ? '↑' : '↓';
        // Inverted: increase = bad (red), decrease = good (green)
        $color = $change >= 0 ? 'text-rose-600' : 'text-emerald-600';
        $pct = number_format(abs($change), 1) . '%';

        return new HtmlString(
            "<span class='text-xl font-bold text-gray-900 dark:text-white'>{$formatted}</span> "
            . "<span class='text-sm {$color}'>{$arrow} {$pct} vs prior period</span>"
        );
    }

    /**
     * Default chart options — hide legend, y-axis starts at zero.
     */
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
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

    /**
     * Build a solid line dataset.
     */
    protected function makeLineDataset(string $label, array $data, string $color): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => $color,
            'backgroundColor' => $color . '1A',
            'fill' => true,
            'borderWidth' => 2,
        ];
    }

    /**
     * Build a dashed comparison line dataset.
     */
    protected function makeComparisonDataset(string $label, array $data): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => '#9ca3af',
            'backgroundColor' => 'transparent',
            'fill' => false,
            'borderWidth' => 1.5,
            'borderDash' => [5, 5],
            'pointRadius' => 0,
        ];
    }

    /**
     * Build a bar dataset with per-bar colors.
     */
    protected function makeBarDataset(string $label, array $data, array $colors): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'backgroundColor' => $colors,
            'borderWidth' => 0,
        ];
    }

    /**
     * Pad/trim array to match target length (for aligning previous period to current).
     */
    protected function padArray(array $data, int $targetLength): array
    {
        $current = count($data);

        if ($current >= $targetLength) {
            return array_slice($data, 0, $targetLength);
        }

        return array_merge(array_fill(0, $targetLength - $current, 0), $data);
    }

    /**
     * Fill null values in a running-total array with the last known value.
     */
    protected function fillNulls(array $data): array
    {
        $last = 0;

        foreach ($data as $i => $value) {
            if ($value === null) {
                $data[$i] = $last;
            } else {
                $last = $value;
            }
        }

        return $data;
    }
}
