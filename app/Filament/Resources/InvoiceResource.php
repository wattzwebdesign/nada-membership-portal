<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
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
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'open' => 'Open',
                                'paid' => 'Paid',
                                'void' => 'Void',
                                'uncollectible' => 'Uncollectible',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('amount_due_cents')
                            ->label('Amount Due (cents)')
                            ->numeric()
                            ->required()
                            ->suffix('cents')
                            ->helperText('Enter amount in cents (e.g., 9999 = $99.99)'),
                        Forms\Components\TextInput::make('amount_paid_cents')
                            ->label('Amount Paid (cents)')
                            ->numeric()
                            ->suffix('cents'),
                        Forms\Components\TextInput::make('currency')
                            ->default('usd')
                            ->maxLength(3),
                    ])->columns(3),

                Forms\Components\Section::make('Billing Period')
                    ->schema([
                        Forms\Components\DateTimePicker::make('period_start'),
                        Forms\Components\DateTimePicker::make('period_end'),
                        Forms\Components\DateTimePicker::make('paid_at'),
                    ])->columns(3),

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
                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('amount_due_cents')
                    ->label('Amount Due')
                    ->formatStateUsing(fn (?int $state): string => '$' . number_format(($state ?? 0) / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid_cents')
                    ->label('Amount Paid')
                    ->formatStateUsing(fn (?int $state): string => '$' . number_format(($state ?? 0) / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_start')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('period_end')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Unpaid'),
                Tables\Columns\TextColumn::make('stripe_invoice_id')
                    ->label('Stripe ID')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
