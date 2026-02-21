<?php

namespace App\Filament\Exports;

use App\Models\EventRegistration;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EventRegistrationExporter extends Exporter
{
    protected static ?string $model = EventRegistration::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('registration_number'),
            ExportColumn::make('event.title')
                ->label('Event'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone')
                ->enabledByDefault(false),
            ExportColumn::make('status')
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('payment_status')
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('total_amount_cents')
                ->label('Total')
                ->formatStateUsing(fn ($state) => $state ? number_format($state / 100, 2) : '0.00'),
            ExportColumn::make('is_member_pricing')
                ->label('Member Pricing')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('checked_in_at')
                ->label('Checked In'),
            ExportColumn::make('created_at')
                ->label('Registered At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your event registration export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
