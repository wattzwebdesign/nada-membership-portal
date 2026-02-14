<?php

namespace App\Filament\Resources;

use App\Enums\PlanType;
use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plan Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('plan_type')
                            ->options(PlanType::class)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price_cents')
                            ->label('Price')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) round((float) $state * 100) : null),
                        Forms\Components\Select::make('billing_interval')
                            ->options([
                                'month' => 'Month',
                                'year' => 'Year',
                                'week' => 'Week',
                                'day' => 'Day',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('billing_interval_count')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(1),
                    ])->columns(2),

                Forms\Components\Section::make('Access Control')
                    ->schema([
                        Forms\Components\TextInput::make('role_required')
                            ->maxLength(255)
                            ->helperText('Role required to see this plan (e.g., registered_trainer)'),
                        Forms\Components\TextInput::make('discount_required')
                            ->maxLength(255)
                            ->helperText('Discount type required (e.g., student, senior)'),
                    ])->columns(2),

                Forms\Components\Section::make('Visibility')
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible to Users')
                            ->default(true),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plan_type')
                    ->badge()
                    ->formatStateUsing(fn (PlanType $state) => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_interval')
                    ->formatStateUsing(fn (string $state, Plan $record): string => $record->billing_label)
                    ->label('Billing'),
                Tables\Columns\TextColumn::make('role_required')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->label('Visible'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Subscribers')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('plan_type')
                    ->options(PlanType::class),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visible'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
