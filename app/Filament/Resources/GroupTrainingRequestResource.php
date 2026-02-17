<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupTrainingRequestResource\Pages;
use App\Models\GroupTrainingRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GroupTrainingRequestResource extends Resource
{
    protected static ?string $model = GroupTrainingRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Training & Certificates';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'training_name';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Training Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('training_name'),
                        Infolists\Components\TextEntry::make('training_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('training_city'),
                        Infolists\Components\TextEntry::make('training_state'),
                        Infolists\Components\TextEntry::make('trainer.full_name')
                            ->label('Trainer'),
                    ])->columns(2),

                Infolists\Components\Section::make('Company Contact')
                    ->schema([
                        Infolists\Components\TextEntry::make('company_full_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('company_email')
                            ->label('Email'),
                    ])->columns(2),

                Infolists\Components\Section::make('Payment')
                    ->schema([
                        Infolists\Components\TextEntry::make('number_of_tickets')
                            ->label('Tickets'),
                        Infolists\Components\TextEntry::make('cost_per_ticket_cents')
                            ->label('Cost Per Ticket')
                            ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('transaction_fee_cents')
                            ->label('Transaction Fee')
                            ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('total_amount_cents')
                            ->label('Total')
                            ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending_payment' => 'warning',
                                'failed' => 'danger',
                                'expired' => 'gray',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('paid_at')
                            ->dateTime()
                            ->placeholder('Not yet paid'),
                        Infolists\Components\TextEntry::make('stripe_checkout_session_id')
                            ->label('Stripe Session')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent')
                            ->placeholder('—'),
                    ])->columns(2),

                Infolists\Components\Section::make('Team Members')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('members')
                            ->schema([
                                Infolists\Components\TextEntry::make('full_name')
                                    ->label('Name'),
                                Infolists\Components\TextEntry::make('email'),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('training_name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('company_full_name')
                    ->label('Company Contact')
                    ->searchable(['company_first_name', 'company_last_name'])
                    ->sortable('company_last_name'),
                Tables\Columns\TextColumn::make('trainer.full_name')
                    ->label('Trainer')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('training_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_tickets')
                    ->label('Tickets')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount_cents')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending_payment' => 'warning',
                        'failed' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroupTrainingRequests::route('/'),
            'view' => Pages\ViewGroupTrainingRequest::route('/{record}'),
        ];
    }
}
