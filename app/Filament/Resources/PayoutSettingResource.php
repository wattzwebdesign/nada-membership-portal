<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayoutSettingResource\Pages;
use App\Models\PayoutSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayoutSettingResource extends Resource
{
    protected static ?string $model = PayoutSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payout Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'trainer' => 'Trainer',
                                'vendor' => 'Vendor',
                            ])
                            ->required()
                            ->default('trainer'),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->label('User')
                            ->helperText('Leave blank for global default setting'),
                        Forms\Components\TextInput::make('platform_percentage')
                            ->label('Platform Percentage')
                            ->numeric()
                            ->required()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01),
                        Forms\Components\TextInput::make('payee_percentage')
                            ->label('Payee Percentage')
                            ->numeric()
                            ->required()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'trainer' => 'info',
                        'vendor' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->placeholder('Global Default')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform_percentage')
                    ->label('Platform %')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payee_percentage')
                    ->label('Payee %')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'trainer' => 'Trainer',
                        'vendor' => 'Vendor',
                    ]),
                Tables\Filters\Filter::make('global_default')
                    ->query(fn ($query) => $query->whereNull('user_id'))
                    ->label('Global Default Only')
                    ->toggle(),
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
            'index' => Pages\ListPayoutSettings::route('/'),
            'create' => Pages\CreatePayoutSetting::route('/create'),
            'edit' => Pages\EditPayoutSetting::route('/{record}/edit'),
        ];
    }
}
