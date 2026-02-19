<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TrainerApplicationResource;
use App\Models\TrainerApplication;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingTrainerApplicationsWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected int $defaultPaginationPageOption = 5;

    public function getTableHeading(): string
    {
        return 'Pending Trainer Applications';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TrainerApplication::query()
                    ->pending()
                    ->with('user')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied')
                    ->date(),
                Tables\Columns\TextColumn::make('wait_time')
                    ->label('Wait Time')
                    ->state(fn (TrainerApplication $record): string => $record->created_at->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (TrainerApplication $record): string => TrainerApplicationResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->emptyStateHeading('No pending trainer applications')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->paginated([5]);
    }
}
