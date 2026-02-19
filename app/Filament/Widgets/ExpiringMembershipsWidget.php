<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\Subscription;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ExpiringMembershipsWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected int $defaultPaginationPageOption = 5;

    public function getTableHeading(): string
    {
        return 'Memberships Expiring in 30 Days';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->active()
                    ->with(['user', 'plan'])
                    ->whereBetween('current_period_end', [now(), now()->addDays(30)])
                    ->orderBy('current_period_end')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Member'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan'),
                Tables\Columns\TextColumn::make('current_period_end')
                    ->label('Expires')
                    ->date(),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Days Remaining')
                    ->state(fn (Subscription $record): string => now()->diffInDays($record->current_period_end) . ' days'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (Subscription $record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->emptyStateHeading('No memberships expiring soon')
            ->emptyStateIcon('heroicon-o-clock')
            ->paginated([5]);
    }
}
