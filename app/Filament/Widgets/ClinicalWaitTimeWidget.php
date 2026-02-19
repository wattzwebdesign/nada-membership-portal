<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClinicalResource;
use App\Models\Clinical;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ClinicalWaitTimeWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected int $defaultPaginationPageOption = 5;

    public function getTableHeading(): string
    {
        return 'Clinical Submissions Awaiting Review';
    }

    public function getTableDescription(): ?string
    {
        $dates = Clinical::whereIn('status', ['submitted', 'under_review'])->pluck('created_at');

        if ($dates->isEmpty()) {
            return null;
        }

        $days = (int) round($dates->avg(fn ($date) => now()->diffInDays($date)));

        return "Average wait time: {$days} " . str('day')->plural($days);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Clinical::query()
                    ->whereIn('status', ['submitted', 'under_review'])
                    ->oldest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Applicant')
                    ->state(fn (Clinical $record): string => "{$record->first_name} {$record->last_name}"),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('trainer.email')
                    ->label('Trainer')
                    ->placeholder('Not assigned'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date(),
                Tables\Columns\TextColumn::make('wait_time')
                    ->label('Wait Time')
                    ->state(fn (Clinical $record): string => $record->created_at->diffForHumans()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Clinical $record): string => ClinicalResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->emptyStateHeading('No pending clinical submissions')
            ->emptyStateIcon('heroicon-o-heart')
            ->paginated([5]);
    }
}
