<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrdersWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected int $defaultPaginationPageOption = 10;

    public function getTableHeading(): string
    {
        return 'Recent Orders';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->where('status', '!=', OrderStatus::Pending)
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #'),
                Tables\Columns\TextColumn::make('customer_full_name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('total_formatted')
                    ->label('Total'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->paginated([10]);
    }
}
