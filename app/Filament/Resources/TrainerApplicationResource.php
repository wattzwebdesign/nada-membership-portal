<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerApplicationResource\Pages;
use App\Models\TrainerApplication;
use App\Notifications\TrainerApplicationApprovedNotification;
use App\Notifications\TrainerApplicationDeniedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainerApplicationResource extends Resource
{
    protected static ?string $model = TrainerApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'status';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.email', 'user.first_name', 'user.last_name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('user');
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return ($record->user?->first_name ?? '') . ' ' . ($record->user?->last_name ?? '');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'User' => $record->user?->email,
            'Status' => $record->status ? ucfirst($record->status) : null,
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }

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

                Forms\Components\Section::make('Uploaded Documents')
                    ->schema([
                        Forms\Components\Placeholder::make('letter_of_nomination_download')
                            ->label('Letter of Nomination')
                            ->content(function (TrainerApplication $record): string {
                                $media = $record->getFirstMedia('letter_of_nomination');
                                if ($media) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<a href="' . $media->getUrl() . '" target="_blank" class="text-primary-600 hover:underline">' .
                                        e($media->file_name) . ' (' . number_format($media->size / 1024, 1) . ' KB)</a>'
                                    );
                                }
                                return 'No file uploaded';
                            }),
                        Forms\Components\Placeholder::make('application_submission_download')
                            ->label('Application Submission')
                            ->content(function (TrainerApplication $record): string {
                                $media = $record->getFirstMedia('application_submission');
                                if ($media) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<a href="' . $media->getUrl() . '" target="_blank" class="text-primary-600 hover:underline">' .
                                        e($media->file_name) . ' (' . number_format($media->size / 1024, 1) . ' KB)</a>'
                                    );
                                }
                                return 'No file uploaded';
                            }),
                    ])
                    ->visible(fn (?TrainerApplication $record): bool => $record !== null),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\TextInput::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent')
                            ->disabled(),
                        Forms\Components\Placeholder::make('amount_display')
                            ->label('Amount Paid')
                            ->content(fn (TrainerApplication $record): string => '$' . number_format($record->amount_paid_cents / 100, 2)),
                        Forms\Components\Placeholder::make('invoice_link')
                            ->label('Invoice')
                            ->content(function (TrainerApplication $record): string {
                                if ($record->invoice) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<a href="' . route('filament.admin.resources.invoices.edit', $record->invoice_id) . '" class="text-primary-600 hover:underline">' .
                                        e($record->invoice->number) . '</a>'
                                    );
                                }
                                return 'No invoice';
                            }),
                    ])
                    ->visible(fn (?TrainerApplication $record): bool => $record !== null && $record->stripe_payment_intent_id),

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Applicant')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.full_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('Phone')
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Applied At')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Uploaded Documents')
                    ->schema([
                        Infolists\Components\TextEntry::make('letter_of_nomination_file')
                            ->label('Letter of Nomination')
                            ->state(function (TrainerApplication $record): string {
                                $media = $record->getFirstMedia('letter_of_nomination');
                                return $media ? $media->file_name : 'No file uploaded';
                            })
                            ->url(fn (TrainerApplication $record) => $record->getFirstMedia('letter_of_nomination')?->getUrl())
                            ->openUrlInNewTab()
                            ->color(fn (TrainerApplication $record) => $record->getFirstMedia('letter_of_nomination') ? 'primary' : 'gray'),
                        Infolists\Components\TextEntry::make('application_submission_file')
                            ->label('Application Submission')
                            ->state(function (TrainerApplication $record): string {
                                $media = $record->getFirstMedia('application_submission');
                                return $media ? $media->file_name : 'No file uploaded';
                            })
                            ->url(fn (TrainerApplication $record) => $record->getFirstMedia('application_submission')?->getUrl())
                            ->openUrlInNewTab()
                            ->color(fn (TrainerApplication $record) => $record->getFirstMedia('application_submission') ? 'primary' : 'gray'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Payment')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount_paid_cents')
                            ->label('Amount Paid')
                            ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('invoice.number')
                            ->label('Invoice')
                            ->url(fn (TrainerApplication $record) => $record->invoice_id
                                ? route('filament.admin.resources.invoices.edit', $record->invoice_id)
                                : null)
                            ->color('primary')
                            ->placeholder('No invoice'),
                    ])
                    ->columns(3)
                    ->visible(fn (TrainerApplication $record): bool => (bool) $record->stripe_payment_intent_id),

                Infolists\Components\Section::make('Review Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'denied' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('reviewer.email')
                            ->label('Reviewed By')
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('reviewed_at')
                            ->label('Reviewed At')
                            ->dateTime()
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->placeholder('None')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
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
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('amount_paid_cents')
                    ->label('Paid')
                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2))
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
                Tables\Actions\ViewAction::make(),
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
                        $user = $record->user;
                        $user->trainer_application_status = 'approved';
                        $user->trainer_approved_at = now();
                        $user->trainer_approved_by = auth()->id();
                        $user->save();

                        try {
                            $record->user->notify(new TrainerApplicationApprovedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send notification: TrainerApplicationApprovedNotification', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title('Trainer Application Approved')
                            ->body('The user has been assigned the Registered Trainer role.')
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

                        $record->user->trainer_application_status = 'denied';
                        $record->user->save();

                        try {
                            $record->user->notify(new TrainerApplicationDeniedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send notification: TrainerApplicationDeniedNotification', ['error' => $e->getMessage()]);
                        }

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
            'view' => Pages\ViewTrainerApplication::route('/{record}'),
            'edit' => Pages\EditTrainerApplication::route('/{record}/edit'),
        ];
    }
}
