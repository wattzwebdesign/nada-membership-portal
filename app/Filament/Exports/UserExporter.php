<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('organization'),
            ExportColumn::make('address_line_1'),
            ExportColumn::make('address_line_2'),
            ExportColumn::make('city'),
            ExportColumn::make('state'),
            ExportColumn::make('zip'),
            ExportColumn::make('country'),
            ExportColumn::make('discount_type')
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('discount_approved')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('trainer_application_status'),
            ExportColumn::make('vendor_application_status'),
            ExportColumn::make('roles')
                ->state(fn (User $record) => $record->roles->pluck('name')->join(', ')),
            ExportColumn::make('nda_accepted_at'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
