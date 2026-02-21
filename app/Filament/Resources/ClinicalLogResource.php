<?php

namespace App\Filament\Resources;

use App\Enums\ClinicalLogStatus;
use App\Enums\RegistrationStatus;
use App\Filament\Resources\ClinicalLogResource\Pages;
use App\Models\ClinicalLog;
use App\Notifications\CertificateReadyNotification;
use App\Notifications\ClinicalLogApprovedNotification;
use App\Notifications\ClinicalLogRejectedNotification;
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

class ClinicalLogResource extends Resource
{
    protected static ?string $model = ClinicalLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Training & Certificates';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Clinical Log Books';

    protected static ?string $pluralModelLabel = 'clinical log books';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Log Book Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('trainer_id')
                            ->relationship('trainer', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->options(collect(ClinicalLogStatus::cases())->mapWithKeys(
                                fn ($s) => [$s->value => $s->getLabel()]
                            ))
                            ->required()
                            ->default('in_progress'),
                    ])->columns(2),

                Forms\Components\Section::make('Review')
                    ->schema([
                        Forms\Components\Textarea::make('reviewer_notes')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Log Book Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.full_name')
                            ->label('Member'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Member Email'),
                        Infolists\Components\TextEntry::make('trainer.full_name')
                            ->label('Trainer')
                            ->placeholder('Not assigned'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (ClinicalLogStatus $state): string => $state->getColor()),
                    ])->columns(2),

                Infolists\Components\Section::make('Hours')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_hours_display')
                            ->label('Total Hours')
                            ->state(fn (ClinicalLog $record): string => number_format($record->total_hours, 1) . ' / ' . number_format($record->hours_threshold, 0) . ' hrs'),
                        Infolists\Components\TextEntry::make('entries_count')
                            ->label('Entries')
                            ->state(fn (ClinicalLog $record): string => (string) $record->entries()->count()),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->dateTime()
                            ->placeholder('Not submitted'),
                    ])->columns(3),

                Infolists\Components\Section::make('Entries')
                    ->schema([
                        Infolists\Components\TextEntry::make('entries_table')
                            ->label('')
                            ->state(function (ClinicalLog $record): HtmlString {
                                $entries = $record->entries()->with('media')->orderBy('date')->get();

                                if ($entries->isEmpty()) {
                                    return new HtmlString('<span class="text-gray-500">No entries.</span>');
                                }

                                $rows = $entries->map(function ($e) {
                                    $files = $e->getMedia('entry_attachments')->map(function ($m) {
                                        $url = e($m->getUrl());
                                        $name = e($m->file_name);
                                        return "<a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 hover:underline text-xs\">{$name}</a>";
                                    })->join(', ') ?: '<span class="text-gray-400">--</span>';

                                    return '<tr class="border-b border-gray-100">'
                                        . '<td class="py-2 pr-3 text-sm">' . $e->date->format('M j, Y') . '</td>'
                                        . '<td class="py-2 pr-3 text-sm">' . e($e->location) . '</td>'
                                        . '<td class="py-2 pr-3 text-sm">' . e($e->protocol) . '</td>'
                                        . '<td class="py-2 pr-3 text-sm font-medium">' . number_format($e->hours, 2) . '</td>'
                                        . '<td class="py-2 text-sm">' . $files . '</td>'
                                        . '</tr>';
                                })->join('');

                                return new HtmlString(
                                    '<table class="w-full"><thead><tr class="border-b border-gray-200 text-xs text-gray-500 uppercase">'
                                    . '<th class="text-left py-2 pr-3">Date</th><th class="text-left py-2 pr-3">Location</th>'
                                    . '<th class="text-left py-2 pr-3">Protocol</th><th class="text-left py-2 pr-3">Hours</th>'
                                    . '<th class="text-left py-2">Files</th></tr></thead><tbody>' . $rows . '</tbody></table>'
                                );
                            })
                            ->html(),
                    ]),

                Infolists\Components\Section::make('Review')
                    ->schema([
                        Infolists\Components\TextEntry::make('reviewer.full_name')
                            ->label('Reviewed By')
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('reviewed_at')
                            ->dateTime()
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('reviewer_notes')
                            ->placeholder('None')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
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
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Member')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainer.full_name')
                    ->label('Trainer')
                    ->placeholder('Not assigned')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Hours')
                    ->state(fn (ClinicalLog $record): string => number_format($record->total_hours, 1))
                    ->sortable(query: fn ($query, $direction) => $query->withSum('entries', 'hours')->orderBy('entries_sum_hours', $direction)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ClinicalLogStatus $state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(ClinicalLogStatus::cases())->mapWithKeys(
                        fn ($s) => [$s->value => $s->getLabel()]
                    )),
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
                    ->visible(fn (ClinicalLog $record): bool => $record->status === ClinicalLogStatus::Completed)
                    ->action(function (ClinicalLog $record) {
                        $record->update([
                            'status' => ClinicalLogStatus::Approved,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        try {
                            $record->user->notify(new ClinicalLogApprovedNotification($record));
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send ClinicalLogApprovedNotification', ['error' => $e->getMessage()]);
                        }
                        Notification::make()->title('Clinical Log Book Approved')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ClinicalLog $record): bool => $record->status === ClinicalLogStatus::Completed)
                    ->form([
                        Forms\Components\Textarea::make('reviewer_notes')
                            ->label('Notes for Member')
                            ->required(),
                    ])
                    ->action(function (ClinicalLog $record, array $data) {
                        $record->update([
                            'status' => ClinicalLogStatus::InProgress,
                            'reviewer_notes' => $data['reviewer_notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'completed_at' => null,
                        ]);
                        try {
                            $record->user->notify(new ClinicalLogRejectedNotification($record));
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send ClinicalLogRejectedNotification', ['error' => $e->getMessage()]);
                        }
                        Notification::make()->title('Clinical Log Book Returned to Member')->success()->send();
                    }),
                Tables\Actions\Action::make('issue_certificate')
                    ->label('Issue Certificate')
                    ->icon('heroicon-o-document-check')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Issue Certificate')
                    ->modalDescription('Are you sure you want to issue a NADA certificate for this member? This action cannot be undone.')
                    ->visible(fn (ClinicalLog $record): bool => $record->status === ClinicalLogStatus::Approved && $record->user && ! $record->user->certificates()->exists())
                    ->action(function (ClinicalLog $record) {
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
            'index' => Pages\ListClinicalLogs::route('/'),
            'create' => Pages\CreateClinicalLog::route('/create'),
            'view' => Pages\ViewClinicalLog::route('/{record}'),
            'edit' => Pages\EditClinicalLog::route('/{record}/edit'),
        ];
    }
}
