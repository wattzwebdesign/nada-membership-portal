<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PricingCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'pricingCategories';

    protected static ?string $title = 'Pricing';

    protected static ?string $icon = 'heroicon-o-currency-dollar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(2),
                Forms\Components\Toggle::make('is_required')
                    ->label('Required Selection')
                    ->helperText('Registrants must select a package from this category'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),

                Forms\Components\Section::make('Packages')
                    ->schema([
                        Forms\Components\Repeater::make('packages')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->rows(2),
                                Forms\Components\TextInput::make('price_cents')
                                    ->label('Price ($)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) round((float) $state * 100) : null),
                                Forms\Components\TextInput::make('member_price_cents')
                                    ->label('Member Price ($)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) round((float) $state * 100) : null)
                                    ->helperText('Leave blank for no member discount'),
                                Forms\Components\TextInput::make('early_bird_price_cents')
                                    ->label('Early Bird Price ($)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) round((float) $state * 100) : null),
                                Forms\Components\TextInput::make('early_bird_member_price_cents')
                                    ->label('Early Bird Member Price ($)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) round((float) $state * 100) : null),
                                Forms\Components\DateTimePicker::make('early_bird_deadline'),
                                Forms\Components\TextInput::make('max_quantity')
                                    ->numeric()
                                    ->helperText('Leave blank for unlimited'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'New Package'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->boolean()
                    ->label('Required'),
                Tables\Columns\TextColumn::make('packages_count')
                    ->counts('packages')
                    ->label('Packages'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
