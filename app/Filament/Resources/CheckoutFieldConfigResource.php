<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckoutFieldConfigResource\Pages;
use App\Models\CheckoutFieldConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CheckoutFieldConfigResource extends Resource
{
    protected static ?string $model = CheckoutFieldConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $navigationLabel = 'Checkout Fields';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Field Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('field_name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (?CheckoutFieldConfig $record): bool => $record !== null),
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('section')
                            ->options([
                                'customer' => 'Customer',
                                'billing' => 'Billing',
                                'shipping' => 'Shipping',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true),
                        Forms\Components\Toggle::make('is_required')
                            ->label('Required')
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('field_name')
                    ->label('Field Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('section')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'customer' => 'info',
                        'billing' => 'warning',
                        'shipping' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Visible'),
                Tables\Columns\ToggleColumn::make('is_required')
                    ->label('Required'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => Pages\ListCheckoutFieldConfigs::route('/'),
            'edit' => Pages\EditCheckoutFieldConfig::route('/{record}/edit'),
        ];
    }
}
