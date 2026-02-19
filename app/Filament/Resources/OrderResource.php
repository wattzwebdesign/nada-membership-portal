<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function getGloballySearchableAttributes(): array
    {
        return ['order_number', 'customer_first_name', 'customer_last_name', 'customer_email'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Customer' => $record->customer_email,
            'Status' => $record->status?->label(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(OrderStatus::class)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Customer Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Order Number'),
                        Infolists\Components\TextEntry::make('customer_full_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('customer_email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('customer_phone')
                            ->label('Phone')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('Paid At')
                            ->dateTime()
                            ->placeholder('Not paid'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Billing Address')
                    ->schema([
                        Infolists\Components\TextEntry::make('billing_address_line_1')
                            ->label('Address Line 1'),
                        Infolists\Components\TextEntry::make('billing_address_line_2')
                            ->label('Address Line 2')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('billing_city')
                            ->label('City'),
                        Infolists\Components\TextEntry::make('billing_state')
                            ->label('State'),
                        Infolists\Components\TextEntry::make('billing_zip')
                            ->label('Zip'),
                        Infolists\Components\TextEntry::make('billing_country')
                            ->label('Country'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Shipping Address')
                    ->schema([
                        Infolists\Components\TextEntry::make('shipping_address_line_1')
                            ->label('Address Line 1'),
                        Infolists\Components\TextEntry::make('shipping_address_line_2')
                            ->label('Address Line 2')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('shipping_city')
                            ->label('City'),
                        Infolists\Components\TextEntry::make('shipping_state')
                            ->label('State'),
                        Infolists\Components\TextEntry::make('shipping_zip')
                            ->label('Zip'),
                        Infolists\Components\TextEntry::make('shipping_country')
                            ->label('Country'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Order Totals')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal_cents')
                            ->label('Subtotal')
                            ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('shipping_cents')
                            ->label('Shipping')
                            ->formatStateUsing(fn ($state): string => $state ? '$' . number_format($state / 100, 2) : 'Free'),
                        Infolists\Components\TextEntry::make('tax_cents')
                            ->label('Tax')
                            ->formatStateUsing(fn ($state): string => '$' . number_format(($state ?? 0) / 100, 2)),
                        Infolists\Components\TextEntry::make('total_cents')
                            ->label('Total')
                            ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2))
                            ->weight('bold'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Order Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_title')
                                    ->label('Product'),
                                Infolists\Components\TextEntry::make('product_sku')
                                    ->label('SKU')
                                    ->placeholder('N/A'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qty'),
                                Infolists\Components\TextEntry::make('unit_price_cents')
                                    ->label('Unit Price')
                                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2)),
                                Infolists\Components\TextEntry::make('total_cents')
                                    ->label('Total')
                                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2)),
                                Infolists\Components\IconEntry::make('is_digital')
                                    ->boolean()
                                    ->label('Digital'),
                            ])
                            ->columns(6),
                    ]),

                Infolists\Components\Section::make('Vendor Splits')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('vendorOrderSplits')
                            ->schema([
                                Infolists\Components\TextEntry::make('vendorProfile.business_name')
                                    ->label('Vendor'),
                                Infolists\Components\TextEntry::make('subtotal_cents')
                                    ->label('Subtotal')
                                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2)),
                                Infolists\Components\TextEntry::make('platform_fee_cents')
                                    ->label('Platform Fee')
                                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2)),
                                Infolists\Components\TextEntry::make('vendor_payout_cents')
                                    ->label('Vendor Payout')
                                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2)),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                            ])
                            ->columns(5),
                    ]),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_full_name')
                    ->label('Customer')
                    ->searchable(['customer_first_name', 'customer_last_name']),
                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_cents')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not paid'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::class),
                Tables\Filters\Filter::make('paid_at')
                    ->form([
                        Forms\Components\DatePicker::make('paid_from')
                            ->label('Paid From'),
                        Forms\Components\DatePicker::make('paid_until')
                            ->label('Paid Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['paid_from'], fn ($q, $date) => $q->whereDate('paid_at', '>=', $date))
                            ->when($data['paid_until'], fn ($q, $date) => $q->whereDate('paid_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_processing')
                    ->label('Mark Processing')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::Paid]))
                    ->action(function (Order $record) {
                        $record->update(['status' => OrderStatus::Processing]);

                        Notification::make()
                            ->title('Order marked as Processing')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('mark_shipped')
                    ->label('Mark Shipped')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::Processing]))
                    ->action(function (Order $record) {
                        $record->update(['status' => OrderStatus::Shipped]);

                        Notification::make()
                            ->title('Order marked as Shipped')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('mark_delivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::Shipped]))
                    ->action(function (Order $record) {
                        $record->update(['status' => OrderStatus::Delivered]);

                        Notification::make()
                            ->title('Order marked as Delivered')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Order')
                    ->modalDescription('Are you sure you want to cancel this order?')
                    ->visible(fn (Order $record): bool => ! in_array($record->status, [OrderStatus::Canceled, OrderStatus::Refunded, OrderStatus::Delivered]))
                    ->action(function (Order $record) {
                        $record->update(['status' => OrderStatus::Canceled]);

                        Notification::make()
                            ->title('Order Canceled')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
