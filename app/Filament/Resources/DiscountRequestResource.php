<?php

namespace App\Filament\Resources;

use App\Enums\DiscountType;
use App\Filament\Resources\DiscountRequestResource\Pages;
use App\Models\DiscountRequest;
use App\Notifications\DiscountApprovedNotification;
use App\Notifications\DiscountDeniedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiscountRequestResource extends Resource
{
    protected static ?string $model = DiscountRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

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

                        $record->user->notify(new DiscountApprovedNotification($record));

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

                        $record->user->notify(new DiscountDeniedNotification($record));

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
            'edit' => Pages\EditDiscountRequest::route('/{record}/edit'),
        ];
    }
}
