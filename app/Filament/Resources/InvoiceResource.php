<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('number')
                            ->placeholder('Auto-generated')
                            ->helperText('Leave blank to auto-generate (e.g., NADA-00001)')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'open' => 'Open',
                                'paid' => 'Paid',
                                'void' => 'Void',
                                'uncollectible' => 'Uncollectible',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->visible(fn (Get $get): bool => $get('status') === 'paid'),
                    ])->columns(2),

                Forms\Components\Section::make('Line Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('plan_id')
                                    ->label('Plan')
                                    ->options(
                                        Plan::where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(fn (Plan $plan) => [
                                                $plan->id => "{$plan->name} ({$plan->price_formatted})",
                                            ])
                                    )
                                    ->searchable()
                                    ->placeholder('Select a plan (optional)')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state) {
                                            $plan = Plan::find($state);
                                            if ($plan) {
                                                $set('description', $plan->name);
                                                $set('unit_price', number_format($plan->price_cents / 100, 2, '.', ''));
                                                $set('quantity', 1);
                                                $set('total', number_format($plan->price_cents / 100, 2, '.', ''));
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('description')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $qty = (int) ($get('quantity') ?: 1);
                                        $price = (float) ($get('unit_price') ?: 0);
                                        $set('total', number_format($qty * $price, 2, '.', ''));
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price ($)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $qty = (int) ($get('quantity') ?: 1);
                                        $price = (float) ($get('unit_price') ?: 0);
                                        $set('total', number_format($qty * $price, 2, '.', ''));
                                    }),
                                Forms\Components\TextInput::make('total')
                                    ->label('Total ($)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->readOnly(),
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->addActionLabel('Add Line Item')
                            ->reorderable(false)
                            ->live(),
                    ]),

                Forms\Components\Section::make('Totals')
                    ->schema([
                        Forms\Components\Placeholder::make('calculated_total')
                            ->label('Amount Due')
                            ->content(function (Get $get): string {
                                $items = $get('items') ?? [];
                                $total = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
                                return '$' . number_format($total, 2);
                            }),
                        Forms\Components\Placeholder::make('amount_paid_display')
                            ->label('Amount Paid')
                            ->content(fn (?Invoice $record): string => '$' . number_format($record?->amount_paid ?? 0, 2))
                            ->visibleOn('edit'),
                    ])->columns(2),

                Forms\Components\Section::make('Stripe Details')
                    ->schema([
                        Forms\Components\TextInput::make('stripe_invoice_id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('stripe_subscription_id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hosted_invoice_url')
                            ->label('Hosted Invoice URL')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('invoice_pdf_url')
                            ->label('Invoice PDF URL')
                            ->url()
                            ->maxLength(500),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->hiddenOn('create'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Invoice Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('number')
                            ->label('Invoice #')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('User'),
                        Infolists\Components\TextEntry::make('user.full_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'open' => 'warning',
                                'draft' => 'gray',
                                'void' => 'danger',
                                'uncollectible' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('Paid At')
                            ->dateTime()
                            ->placeholder('Unpaid'),
                    ])->columns(3),

                Infolists\Components\Section::make('Line Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\TextEntry::make('description')
                                    ->columnSpan(2),
                                Infolists\Components\TextEntry::make('plan.name')
                                    ->label('Plan')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('quantity'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('Unit Price')
                                    ->money('usd'),
                                Infolists\Components\TextEntry::make('total')
                                    ->money('usd'),
                            ])
                            ->columns(6),
                    ]),

                Infolists\Components\Section::make('Totals')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount_due')
                            ->label('Amount Due')
                            ->money('usd')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('amount_paid')
                            ->label('Amount Paid')
                            ->money('usd')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                    ])->columns(2),

                Infolists\Components\Section::make('Stripe Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('stripe_invoice_id')
                            ->label('Stripe Invoice ID')
                            ->copyable()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('stripe_subscription_id')
                            ->label('Stripe Subscription ID')
                            ->copyable()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('hosted_invoice_url')
                            ->label('Hosted Invoice URL')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('invoice_pdf_url')
                            ->label('Invoice PDF URL')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'open' => 'warning',
                        'draft' => 'gray',
                        'void' => 'danger',
                        'uncollectible' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->label('Amount Due')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Unpaid'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'open' => 'Open',
                        'paid' => 'Paid',
                        'void' => 'Void',
                        'uncollectible' => 'Uncollectible',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_stripe')
                    ->label('View in Stripe')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Invoice $record): ?string => $record->hosted_invoice_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Invoice $record): bool => !empty($record->hosted_invoice_url)),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
