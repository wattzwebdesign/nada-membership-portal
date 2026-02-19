<?php

namespace App\Filament\Resources;

use App\Enums\TrainingStatus;
use App\Enums\TrainingType;
use App\Filament\Resources\TrainingResource\Pages;
use App\Filament\Resources\TrainingResource\RelationManagers;
use App\Models\Training;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\GroupTrainingInviteNotification;
use App\Notifications\TrainingApprovedNotification;
use App\Notifications\TrainingDeniedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Training & Certificates';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'location_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Type' => $record->type?->label(),
            'Status' => $record->status?->label(),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Training::pendingApproval()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

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
                            ->default(TrainingStatus::PendingApproval),
                        Forms\Components\Toggle::make('is_group')
                            ->label('Group Training')
                            ->helperText('Group trainings are invite-only and always free.')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Denial Reason')
                    ->schema([
                        Forms\Components\Textarea::make('denied_reason')
                            ->label('Reason for Denial')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (?Training $record) => $record?->status === TrainingStatus::Denied)
                    ->collapsed(),

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
                            ->label('Price')
                            ->numeric()
                            ->prefix('$')
                            ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) round((float) $state * 100) : null)
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
                Tables\Columns\IconColumn::make('is_group')
                    ->boolean()
                    ->label('Group')
                    ->trueIcon('heroicon-o-user-group')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info')
                    ->falseColor('gray'),
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
                Tables\Filters\TernaryFilter::make('is_group')
                    ->label('Group Training'),
                Tables\Filters\Filter::make('upcoming')
                    ->query(fn (Builder $query) => $query->where('start_date', '>', now()))
                    ->label('Upcoming Only')
                    ->toggle(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Training')
                    ->modalDescription('This will publish the training and notify the trainer. If this is a group training, invitees will also be notified.')
                    ->visible(fn (Training $record) => $record->status === TrainingStatus::PendingApproval && !$record->trashed())
                    ->action(function (Training $record) {
                        $record->update(['status' => TrainingStatus::Published]);

                        // Notify trainer
                        try {
                            $record->trainer->notify(new TrainingApprovedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send TrainingApprovedNotification', ['error' => $e->getMessage()]);
                        }

                        // For group trainings, send invitations
                        if ($record->is_group) {
                            foreach ($record->invitees as $invitee) {
                                try {
                                    Notification::route('mail', $invitee->email)
                                        ->notify(new GroupTrainingInviteNotification($record, $invitee));
                                    $invitee->update(['notified_at' => now()]);
                                } catch (\Throwable $e) {
                                    Log::error('Failed to send GroupTrainingInviteNotification', [
                                        'email' => $invitee->email,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Training approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('denied_reason')
                            ->label('Reason for Denial')
                            ->required()
                            ->maxLength(1000)
                            ->placeholder('Explain why this training is being denied...'),
                    ])
                    ->modalHeading('Deny Training')
                    ->visible(fn (Training $record) => $record->status === TrainingStatus::PendingApproval && !$record->trashed())
                    ->action(function (Training $record, array $data) {
                        $record->update([
                            'status' => TrainingStatus::Denied,
                            'denied_reason' => $data['denied_reason'],
                        ]);

                        // Notify trainer
                        try {
                            $record->trainer->notify(new TrainingDeniedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send TrainingDeniedNotification', ['error' => $e->getMessage()]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Training denied')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InviteesRelationManager::class,
        ];
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
