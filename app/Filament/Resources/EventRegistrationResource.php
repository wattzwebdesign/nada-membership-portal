<?php

namespace App\Filament\Resources;

use App\Enums\EventPaymentStatus;
use App\Enums\RegistrationStatus;
use App\Filament\Exports\EventRegistrationExporter;
use App\Filament\Resources\EventRegistrationResource\Pages;
use App\Models\EventRegistration;
use App\Services\EventRegistrationService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Events';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Registrations';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (RegistrationStatus $state) => $state->label())
                    ->color(fn (RegistrationStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_amount_cents')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state) => '$' . number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_member_pricing')
                    ->boolean()
                    ->label('Member')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->dateTime('M j g:i A')
                    ->placeholder('--')
                    ->label('Check-In'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'title'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(RegistrationStatus::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(EventPaymentStatus::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('check_in')
                        ->label('Check In')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (EventRegistration $record) => ! $record->isCheckedIn() && $record->status !== RegistrationStatus::Canceled)
                        ->action(function (EventRegistration $record) {
                            $record->update([
                                'checked_in_at' => now(),
                                'checked_in_by' => auth()->id(),
                                'status' => RegistrationStatus::Attended,
                            ]);

                            Notification::make()
                                ->title('Checked in: ' . $record->full_name)
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('undo_check_in')
                        ->label('Undo Check-In')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (EventRegistration $record) => $record->isCheckedIn())
                        ->action(function (EventRegistration $record) {
                            $record->update([
                                'checked_in_at' => null,
                                'checked_in_by' => null,
                                'status' => RegistrationStatus::Registered,
                            ]);

                            Notification::make()
                                ->title('Check-in reversed: ' . $record->full_name)
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('cancel')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (EventRegistration $record) => $record->status !== RegistrationStatus::Canceled)
                        ->action(function (EventRegistration $record) {
                            app(EventRegistrationService::class)->cancelRegistration($record);

                            Notification::make()
                                ->title('Registration canceled')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(EventRegistrationExporter::class),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventRegistrations::route('/'),
            'view' => Pages\ViewEventRegistration::route('/{record}'),
        ];
    }
}
