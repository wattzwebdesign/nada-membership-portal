<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\EventPaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\EventRegistration;
use App\Services\EventRegistrationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $icon = 'heroicon-o-user-group';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->searchable()
                    ->sortable(),
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
                    ->formatStateUsing(fn (int $state) => '$' . number_format($state / 100, 2)),
                Tables\Columns\IconColumn::make('is_member_pricing')
                    ->boolean()
                    ->label('Member'),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->dateTime('M j g:i A')
                    ->placeholder('Not checked in')
                    ->label('Checked In'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(RegistrationStatus::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(EventPaymentStatus::class),
                Tables\Filters\TernaryFilter::make('checked_in')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('checked_in_at'),
                        false: fn ($query) => $query->whereNull('checked_in_at'),
                    ),
            ])
            ->actions([
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
                            ->title('Attendee checked in')
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
                            ->title('Check-in reversed')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('comp')
                    ->label('Comp')
                    ->icon('heroicon-o-gift')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (EventRegistration $record) => $record->payment_status === EventPaymentStatus::Unpaid)
                    ->action(function (EventRegistration $record) {
                        $record->update(['payment_status' => EventPaymentStatus::Comped]);

                        Notification::make()
                            ->title('Registration comped')
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
            ]);
    }
}
