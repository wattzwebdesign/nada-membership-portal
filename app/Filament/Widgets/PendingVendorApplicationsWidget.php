<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\VendorApplicationResource;
use App\Models\VendorApplication;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingVendorApplicationsWidget extends TableWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected int $defaultPaginationPageOption = 5;

    public function getTableHeading(): string
    {
        return 'Pending Vendor Applications';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VendorApplication::query()
                    ->pending()
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->state(fn (VendorApplication $record): string => "{$record->first_name} {$record->last_name}"),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business'),
                Tables\Columns\TextColumn::make('what_they_sell')
                    ->label('What They Sell')
                    ->limit(40),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (VendorApplication $record): string => VendorApplicationResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->emptyStateHeading('No pending vendor applications')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->paginated([5]);
    }
}
