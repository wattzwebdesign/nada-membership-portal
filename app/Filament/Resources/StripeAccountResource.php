<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StripeAccountResource\Pages;
use App\Models\StripeAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StripeAccountResource extends Resource
{
    protected static ?string $model = StripeAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Stripe Connect Accounts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('stripe_connect_account_id')
                            ->label('Stripe Connect Account ID')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('default_currency')
                            ->maxLength(3)
                            ->default('usd'),
                    ])->columns(2),

                Forms\Components\Section::make('Onboarding Status')
                    ->schema([
                        Forms\Components\Toggle::make('onboarding_complete')
                            ->label('Onboarding Complete'),
                        Forms\Components\Toggle::make('charges_enabled')
                            ->label('Charges Enabled'),
                        Forms\Components\Toggle::make('payouts_enabled')
                            ->label('Payouts Enabled'),
                        Forms\Components\Toggle::make('details_submitted')
                            ->label('Details Submitted'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stripe_connect_account_id')
                    ->label('Connect ID')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('onboarding_complete')
                    ->boolean()
                    ->label('Onboarded'),
                Tables\Columns\IconColumn::make('charges_enabled')
                    ->boolean()
                    ->label('Charges'),
                Tables\Columns\IconColumn::make('payouts_enabled')
                    ->boolean()
                    ->label('Payouts'),
                Tables\Columns\IconColumn::make('details_submitted')
                    ->boolean()
                    ->label('Details'),
                Tables\Columns\TextColumn::make('default_currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('onboarding_complete')
                    ->label('Onboarding Complete'),
                Tables\Filters\TernaryFilter::make('charges_enabled')
                    ->label('Charges Enabled'),
                Tables\Filters\TernaryFilter::make('payouts_enabled')
                    ->label('Payouts Enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStripeAccounts::route('/'),
            'create' => Pages\CreateStripeAccount::route('/create'),
            'edit' => Pages\EditStripeAccount::route('/{record}/edit'),
        ];
    }
}
