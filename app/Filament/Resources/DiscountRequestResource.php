<?php

namespace App\Filament\Resources;

use App\Enums\DiscountType;
use App\Filament\Resources\DiscountRequestResource\Pages;
use App\Models\DiscountRequest;
use App\Notifications\DiscountApprovedNotification;
use App\Notifications\DiscountDeniedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DiscountRequestResource extends Resource
{
    protected static ?string $model = DiscountRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'school_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.email', 'user.first_name', 'user.last_name', 'school_name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('user');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'User' => $record->user?->email,
            'Status' => $record->status,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('discount_type')
                            ->options(DiscountType::class)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'denied' => 'Denied',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(2),

                Forms\Components\Section::make('Student Details')
                    ->schema([
                        Forms\Components\TextInput::make('school_name')
                            ->label('School Name'),
                        Forms\Components\TextInput::make('years_remaining')
                            ->label('Years Remaining as Student')
                            ->numeric(),
                    ])->columns(2)
                    ->visible(fn ($get) => $get('discount_type') === DiscountType::Student->value),

                Forms\Components\Section::make('Senior Details')
                    ->schema([
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth'),
                    ])
                    ->visible(fn ($get) => $get('discount_type') === DiscountType::Senior->value),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('proof_description')
                            ->label('Proof Description')
                            ->maxLength(2000)
                            ->columnSpanFull(),
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
                Infolists\Components\Section::make('Request Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('User'),
                        Infolists\Components\TextEntry::make('user.full_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('discount_type')
                            ->badge()
                            ->formatStateUsing(fn (DiscountType $state) => $state->label()),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'denied' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Student Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('school_name')
                            ->label('School'),
                        Infolists\Components\TextEntry::make('years_remaining')
                            ->label('Years Remaining as Student'),
                    ])->columns(2)
                    ->visible(fn (DiscountRequest $record) => $record->discount_type === DiscountType::Student),

                Infolists\Components\Section::make('Senior Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->label('Date of Birth')
                            ->date(),
                    ])
                    ->visible(fn (DiscountRequest $record) => $record->discount_type === DiscountType::Senior),

                Infolists\Components\Section::make('Supporting Documents')
                    ->schema([
                        Infolists\Components\TextEntry::make('proof_documents_display')
                            ->label('')
                            ->state(function (DiscountRequest $record): HtmlString {
                                $media = $record->getMedia('proof_documents');

                                if ($media->isEmpty()) {
                                    return new HtmlString('<span class="text-gray-500">No documents uploaded.</span>');
                                }

                                $links = $media->map(function ($item) {
                                    $url = $item->getUrl();
                                    $name = e($item->file_name);
                                    $size = number_format($item->size / 1024, 1);

                                    return "<a href=\"{$url}\" target=\"_blank\" class=\"inline-flex items-center gap-1 text-primary-600 hover:underline\">"
                                        . "{$name} <span class=\"text-gray-400 text-xs\">({$size} KB)</span>"
                                        . "</a>";
                                })->join('<br>');

                                return new HtmlString($links);
                            })
                            ->html(),
                    ]),

                Infolists\Components\Section::make('Additional Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('proof_description')
                            ->label('')
                            ->placeholder('None provided'),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Review')
                    ->schema([
                        Infolists\Components\TextEntry::make('reviewer.full_name')
                            ->label('Reviewed By')
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('reviewed_at')
                            ->dateTime()
                            ->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->placeholder('None')
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_type')
                    ->badge()
                    ->formatStateUsing(fn (DiscountType $state) => $state->label()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'denied' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('school_name')
                    ->label('School')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('DOB')
                    ->date()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('proof_description')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'denied' => 'Denied',
                    ]),
                Tables\Filters\SelectFilter::make('discount_type')
                    ->options(DiscountType::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Discount Request')
                    ->modalDescription('This will approve the discount and update the user\'s discount status.')
                    ->visible(fn (DiscountRequest $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes (optional)')
                            ->maxLength(2000),
                    ])
                    ->action(function (DiscountRequest $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->user->update([
                            'discount_type' => $record->discount_type,
                            'discount_approved' => true,
                            'discount_approved_at' => now(),
                            'discount_approved_by' => auth()->id(),
                        ]);

                        try {
                            $record->user->notify(new DiscountApprovedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send notification: DiscountApprovedNotification', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title('Discount Request Approved')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Deny Discount Request')
                    ->visible(fn (DiscountRequest $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Denial Reason')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (DiscountRequest $record, array $data) {
                        $record->update([
                            'status' => 'denied',
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        try {
                            $record->user->notify(new DiscountDeniedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send notification: DiscountDeniedNotification', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title('Discount Request Denied')
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
            'index' => Pages\ListDiscountRequests::route('/'),
            'create' => Pages\CreateDiscountRequest::route('/create'),
            'view' => Pages\ViewDiscountRequest::route('/{record}'),
            'edit' => Pages\EditDiscountRequest::route('/{record}/edit'),
        ];
    }
}
