<?php

namespace App\Filament\Pages\EventReports\Widgets;

use App\Enums\EventPaymentStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalEvents = Event::count();
        $totalRegistrations = EventRegistration::where('status', '!=', 'canceled')->count();
        $totalRevenue = EventRegistration::where('payment_status', EventPaymentStatus::Paid->value)
            ->sum('total_amount_cents');

        $checkedIn = EventRegistration::whereNotNull('checked_in_at')->count();
        $checkInRate = $totalRegistrations > 0
            ? round(($checkedIn / $totalRegistrations) * 100, 1)
            : 0;

        return [
            Stat::make('Total Events', $totalEvents)
                ->icon('heroicon-o-calendar-days'),
            Stat::make('Total Registrations', $totalRegistrations)
                ->icon('heroicon-o-ticket'),
            Stat::make('Total Revenue', '$' . number_format($totalRevenue / 100, 2))
                ->icon('heroicon-o-currency-dollar'),
            Stat::make('Avg Check-In Rate', $checkInRate . '%')
                ->icon('heroicon-o-check-badge'),
        ];
    }
}
