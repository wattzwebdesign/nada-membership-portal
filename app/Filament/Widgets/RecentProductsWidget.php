<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentProductsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected int $defaultPaginationPageOption = 10;

    public function getTableHeading(): string
    {
        return 'Recently Published Products';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->active()
                    ->with(['vendorProfile', 'category'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->url(fn (Product $record): string => route('public.shop.show', $record))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('vendorProfile.business_name')
                    ->label('Vendor'),
                Tables\Columns\TextColumn::make('price_formatted')
                    ->label('Price'),
                Tables\Columns\TextColumn::make('member_price_formatted')
                    ->label('Member Price')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date(),
            ])
            ->actions([])
            ->emptyStateHeading('No active products')
            ->emptyStateIcon('heroicon-o-cube')
            ->paginated([10]);
    }
}
