<?php

namespace App\Filament\Widgets;

use App\Enums\DiscountType;
use App\Filament\Resources\DiscountRequestResource;
use App\Models\DiscountRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingDiscountRequestsWidget extends TableWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected int $defaultPaginationPageOption = 5;

    public function getTableHeading(): string
    {
        return 'Pending Discount Requests';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DiscountRequest::query()
                    ->pending()
                    ->with('user')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (DiscountType $state) => $state->label())
                    ->color(fn (DiscountType $state): string => match ($state) {
                        DiscountType::Student => 'info',
                        DiscountType::Senior => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date(),
                Tables\Columns\TextColumn::make('wait_time')
                    ->label('Wait Time')
                    ->state(fn (DiscountRequest $record): string => $record->created_at->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (DiscountRequest $record): string => DiscountRequestResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->emptyStateHeading('No pending discount requests')
            ->emptyStateIcon('heroicon-o-tag')
            ->paginated([5]);
    }
}
