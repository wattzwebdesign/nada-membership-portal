<?php

namespace App\Filament\Resources;

use App\Enums\TrainingStatus;
use App\Enums\TrainingType;
use App\Filament\Resources\TrainingResource\Pages;
use App\Models\Training;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Training & Certificates';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Training Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('trainer_id')
                            ->relationship('trainer', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->options(TrainingType::class)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(TrainingStatus::class)
                            ->required()
                            ->default(TrainingStatus::Draft),
                    ])->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('location_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location_address')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('virtual_link')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Zoom/Meet link for virtual or hybrid trainings'),
                    ])->columns(2),

                Forms\Components\Section::make('Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->required()
                            ->after('start_date'),
                        Forms\Components\TextInput::make('timezone')
                            ->maxLength(50)
                            ->default('America/New_York'),
                        Forms\Components\TextInput::make('max_attendees')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->helperText('Leave blank for unlimited'),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\Toggle::make('is_paid')
                            ->label('Is Paid Training')
                            ->reactive(),
                        Forms\Components\TextInput::make('price_cents')
                            ->label('Price (cents)')
                            ->numeric()
                            ->suffix('cents')
                            ->helperText('Enter price in cents (e.g., 5000 = $50.00)')
                            ->visible(fn (Forms\Get $get) => $get('is_paid')),
                        Forms\Components\TextInput::make('currency')
                            ->default('usd')
                            ->maxLength(3)
                            ->visible(fn (Forms\Get $get) => $get('is_paid')),
                        Forms\Components\TextInput::make('stripe_price_id')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('is_paid')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('trainer.email')
                    ->label('Trainer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (TrainingType $state) => $state->label()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (TrainingStatus $state) => $state->label())
                    ->color(fn (TrainingStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('max_attendees')
                    ->label('Max')
                    ->placeholder('Unlimited'),
                Tables\Columns\TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Registrations')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->label('Paid'),
                Tables\Columns\TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn (?int $state, Training $record): string => $record->price_formatted)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(TrainingType::class),
                Tables\Filters\SelectFilter::make('status')
                    ->options(TrainingStatus::class),
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Paid'),
                Tables\Filters\Filter::make('upcoming')
                    ->query(fn (Builder $query) => $query->where('start_date', '>', now()))
                    ->label('Upcoming Only')
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
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
