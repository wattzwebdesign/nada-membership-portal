<?php

namespace App\Filament\Resources;

use App\Enums\RegistrationStatus;
use App\Filament\Resources\TrainingRegistrationResource\Pages;
use App\Models\TrainingRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingRegistrationResource extends Resource
{
    protected static ?string $model = TrainingRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Training & Certificates';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Registrations';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.email', 'user.first_name', 'training.title'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'training']);
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return ($record->user?->first_name ?? '') . ' ' . ($record->user?->last_name ?? '') . ' — ' . ($record->training?->title ?? '');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'User' => $record->user?->email,
            'Training' => $record->training?->title,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Registration Details')
                    ->schema([
                        Forms\Components\Select::make('training_id')
                            ->relationship('training', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(RegistrationStatus::class)
                            ->required()
                            ->default(RegistrationStatus::Registered),
                    ])->columns(2),

                Forms\Components\Section::make('Completion')
                    ->schema([
                        Forms\Components\DateTimePicker::make('completed_at'),
                        Forms\Components\Select::make('marked_complete_by')
                            ->relationship('markedCompleteBy', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('certificate_id')
                            ->relationship('certificate', 'certificate_code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Payment')
                    ->schema([
                        Forms\Components\TextInput::make('amount_paid_cents')
                            ->label('Amount Paid')
                            ->numeric()
                            ->prefix('$')
                            ->formatStateUsing(fn (?int $state): ?string => $state ? number_format($state / 100, 2, '.', '') : null)
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state ? (int) round((float) $state * 100) : null),
                        Forms\Components\TextInput::make('stripe_payment_intent_id')
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('training.title')
                    ->label('Training')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (RegistrationStatus $state) => $state->label())
                    ->color(fn (RegistrationStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not completed'),
                Tables\Columns\TextColumn::make('amount_paid_cents')
                    ->label('Amount Paid')
                    ->formatStateUsing(fn (?int $state): string => $state ? '$' . number_format($state / 100, 2) : 'Free')
                    ->sortable(),
                Tables\Columns\TextColumn::make('certificate.certificate_code')
                    ->label('Certificate')
                    ->placeholder('None')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(RegistrationStatus::class),
                Tables\Filters\SelectFilter::make('training')
                    ->relationship('training', 'title'),
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
            'index' => Pages\ListTrainingRegistrations::route('/'),
            'create' => Pages\CreateTrainingRegistration::route('/create'),
            'edit' => Pages\EditTrainingRegistration::route('/{record}/edit'),
        ];
    }
}
