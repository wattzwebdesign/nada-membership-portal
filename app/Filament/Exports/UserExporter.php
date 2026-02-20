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
            ExportColumn::make('address_line_1')
                ->enabledByDefault(false),
            ExportColumn::make('address_line_2')
                ->enabledByDefault(false),
            ExportColumn::make('city')
                ->enabledByDefault(false),
            ExportColumn::make('state')
                ->enabledByDefault(false),
            ExportColumn::make('zip')
                ->enabledByDefault(false),
            ExportColumn::make('country')
                ->enabledByDefault(false),
            ExportColumn::make('discount_type')
                ->formatStateUsing(fn ($state) => $state?->label() ?? '')
                ->enabledByDefault(false),
            ExportColumn::make('discount_approved')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                ->enabledByDefault(false),
            ExportColumn::make('trainer_application_status')
                ->enabledByDefault(false),
            ExportColumn::make('vendor_application_status')
                ->enabledByDefault(false),
            ExportColumn::make('roles')
                ->state(fn (User $record) => $record->roles->pluck('name')->join(', '))
                ->enabledByDefault(false),
            ExportColumn::make('nda_accepted_at')
                ->enabledByDefault(false),
            ExportColumn::make('created_at')
                ->enabledByDefault(false),
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
