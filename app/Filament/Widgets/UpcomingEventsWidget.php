<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?string $heading = 'Upcoming Events';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('status', 'published')
                    ->where('start_date', '>', now())
                    ->orderBy('start_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->placeholder('Virtual'),
                Tables\Columns\TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Registrations'),
                Tables\Columns\TextColumn::make('max_attendees')
                    ->label('Capacity')
                    ->placeholder('Unlimited'),
            ])
            ->paginated(false);
    }
}
