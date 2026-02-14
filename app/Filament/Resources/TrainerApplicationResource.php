<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerApplicationResource\Pages;
use App\Models\TrainerApplication;
use App\Notifications\TrainerApplicationApprovedNotification;
use App\Notifications\TrainerApplicationDeniedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrainerApplicationResource extends Resource
{
    protected static ?string $model = TrainerApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Applicant')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Application Details')
                    ->schema([
                        Forms\Components\Textarea::make('credentials')
                            ->required()
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('experience_description')
                            ->label('Experience Description')
                            ->required()
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('license_number')
                            ->label('License Number')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Review')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'denied' => 'Denied',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
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
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credentials')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->label('License #')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'denied' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reviewer.email')
                    ->label('Reviewed By')
                    ->placeholder('Pending')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Pending'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Applied At'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'denied' => 'Denied',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Trainer Application')
                    ->modalDescription('This will approve the application and assign the registered_trainer role to the user.')
                    ->visible(fn (TrainerApplication $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes (optional)')
                            ->maxLength(2000),
                    ])
                    ->action(function (TrainerApplication $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->user->assignRole('registered_trainer');
                        $record->user->update([
                            'trainer_application_status' => 'approved',
                            'trainer_approved_at' => now(),
                            'trainer_approved_by' => auth()->id(),
                        ]);

                        $record->user->notify(new TrainerApplicationApprovedNotification($record));

                        Notification::make()
                            ->title('Trainer Application Approved')
                            ->body('The user has been assigned the registered_trainer role.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Deny Trainer Application')
                    ->visible(fn (TrainerApplication $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Denial Reason')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (TrainerApplication $record, array $data) {
                        $record->update([
                            'status' => 'denied',
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->user->update([
                            'trainer_application_status' => 'denied',
                        ]);

                        $record->user->notify(new TrainerApplicationDeniedNotification($record));

                        Notification::make()
                            ->title('Trainer Application Denied')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListTrainerApplications::route('/'),
            'create' => Pages\CreateTrainerApplication::route('/create'),
            'edit' => Pages\EditTrainerApplication::route('/{record}/edit'),
        ];
    }
}
