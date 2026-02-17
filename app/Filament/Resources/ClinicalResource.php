<?php

namespace App\Filament\Resources;

use App\Enums\RegistrationStatus;
use App\Filament\Resources\ClinicalResource\Pages;
use App\Models\Clinical;
use App\Notifications\CertificateReadyNotification;
use App\Services\CertificateService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ClinicalResource extends Resource
{
    protected static ?string $model = Clinical::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Training & Certificates';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Clinical Submissions';

    protected static ?string $pluralModelLabel = 'clinical submissions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Applicant Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Training Details')
                    ->schema([
                        Forms\Components\DatePicker::make('estimated_training_date'),
                        Forms\Components\Select::make('trainer_id')
                            ->relationship('trainer', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Review')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('submitted'),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Applicant Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('User Account'),
                        Infolists\Components\TextEntry::make('first_name'),
                        Infolists\Components\TextEntry::make('last_name'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Contact Email'),
                    ])->columns(2),

                Infolists\Components\Section::make('Training Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('estimated_training_date')
                            ->date()
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('trainer.full_name')
                            ->label('Trainer')
                            ->placeholder('Not assigned'),
                    ])->columns(2),

                Infolists\Components\Section::make('Treatment Logs')
                    ->schema([
                        Infolists\Components\TextEntry::make('treatment_logs_display')
                            ->label('')
                            ->state(function (Clinical $record): HtmlString {
                                $media = $record->getMedia('treatment_logs');

                                if ($media->isEmpty()) {
                                    return new HtmlString('<span class="text-gray-500">No files uploaded.</span>');
                                }

                                $links = $media->map(function ($item) {
                                    $url = $item->getUrl();
                                    $name = e($item->file_name);
                                    $size = number_format($item->size / 1024, 1);
                                    $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zm4.75 9.25a.75.75 0 001.5 0V7.56l1.22 1.22a.75.75 0 001.06-1.06l-2.5-2.5a.75.75 0 00-1.06 0l-2.5 2.5a.75.75 0 001.06 1.06l1.22-1.22v3.69z" clip-rule="evenodd" /></svg>';

                                    return "<a href=\"{$url}\" target=\"_blank\" class=\"inline-flex items-center gap-1 text-primary-600 hover:underline\">"
                                        . "{$icon} {$name} <span class=\"text-gray-400 text-xs\">({$size} KB)</span>"
                                        . "</a>";
                                })->join('<br>');

                                return new HtmlString($links);
                            })
                            ->html(),
                    ]),

                Infolists\Components\Section::make('Review Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'submitted' => 'info',
                                'under_review' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('reviewer.full_name')
                            ->label('Reviewed By')
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('reviewed_at')
                            ->label('Reviewed At')
                            ->dateTime()
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('notes')
                            ->placeholder('None')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted At')
                            ->dateTime(),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estimated_training_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainer.email')
                    ->label('Trainer')
                    ->placeholder('Not assigned')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('trainer')
                    ->relationship('trainer', 'email'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Clinical $record): bool => in_array($record->status, ['submitted', 'under_review']))
                    ->action(function (Clinical $record) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Clinical Submission Approved')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Clinical $record): bool => in_array($record->status, ['submitted', 'under_review']))
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Rejection Reason')
                            ->required(),
                    ])
                    ->action(function (Clinical $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'notes' => $data['notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Clinical Submission Rejected')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('issue_certificate')
                    ->label('Issue Certificate')
                    ->icon('heroicon-o-document-check')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Issue Certificate')
                    ->modalDescription('Are you sure you want to issue a NADA certificate for this member? This action cannot be undone.')
                    ->visible(fn (Clinical $record): bool => $record->status === 'approved' && ! $record->user->certificates()->exists())
                    ->action(function (Clinical $record) {
                        $certService = app(CertificateService::class);

                        $registration = $record->user->trainingRegistrations()
                            ->where('status', RegistrationStatus::Completed)
                            ->first();

                        $certificate = $certService->issueCertificate(
                            user: $record->user,
                            training: $registration?->training,
                            issuedBy: auth()->user(),
                        );

                        if ($registration) {
                            $registration->update(['certificate_id' => $certificate->id]);
                        }

                        try {
                            $record->user->notify(new CertificateReadyNotification($certificate));
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send CertificateReadyNotification', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title('Certificate Issued')
                            ->body("Certificate code: {$certificate->certificate_code}")
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
            'index' => Pages\ListClinicals::route('/'),
            'create' => Pages\CreateClinical::route('/create'),
            'view' => Pages\ViewClinical::route('/{record}'),
            'edit' => Pages\EditClinical::route('/{record}/edit'),
        ];
    }
}
