<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorApplicationResource\Pages;
use App\Models\User;
use App\Models\VendorApplication;
use App\Notifications\VendorApplicationApprovedNotification;
use App\Notifications\VendorApplicationDeniedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VendorApplicationResource extends Resource
{
    protected static ?string $model = VendorApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $recordTitleAttribute = 'business_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'email', 'business_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Email' => $record->email,
            'Status' => $record->status,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

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
                Forms\Components\Section::make('Applicant Information')
                    ->schema([
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
                        Forms\Components\TextInput::make('business_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('what_they_sell')
                            ->label('What They Sell')
                            ->maxLength(2000)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->state(fn (VendorApplication $record): string => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('what_they_sell')
                    ->label('What They Sell')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('website')
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'denied' => 'danger',
                        default => 'gray',
                    }),
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
                    ->modalHeading('Approve Vendor Application')
                    ->modalDescription('This will approve the application and assign the vendor role to the user.')
                    ->visible(fn (VendorApplication $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes (optional)')
                            ->maxLength(2000),
                    ])
                    ->action(function (VendorApplication $record, array $data) {
                        // If no user_id, create a new user
                        if (! $record->user_id) {
                            $user = User::create([
                                'first_name' => $record->first_name,
                                'last_name' => $record->last_name,
                                'email' => $record->email,
                                'password' => bcrypt(Str::random(16)),
                                'email_verified_at' => now(),
                            ]);

                            $record->update(['user_id' => $user->id]);
                        } else {
                            $user = $record->user;
                        }

                        $record->update([
                            'status' => 'approved',
                            'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $user->assignRole('vendor');
                        $user->update([
                            'vendor_application_status' => 'approved',
                        ]);

                        try {
                            $user->notify(new VendorApplicationApprovedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send notification: VendorApplicationApprovedNotification', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title('Vendor Application Approved')
                            ->body('The user has been assigned the Vendor role.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Deny Vendor Application')
                    ->visible(fn (VendorApplication $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Denial Reason')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (VendorApplication $record, array $data) {
                        $record->update([
                            'status' => 'denied',
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        // Notify the user if they have an account
                        $notifiable = $record->user ?? (new User(['email' => $record->email]));

                        if ($record->user) {
                            $record->user->update([
                                'vendor_application_status' => 'denied',
                            ]);
                        }

                        try {
                            $notifiable->notify(new VendorApplicationDeniedNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send notification: VendorApplicationDeniedNotification', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title('Vendor Application Denied')
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
            'index' => Pages\ListVendorApplications::route('/'),
            'create' => Pages\CreateVendorApplication::route('/create'),
            'view' => Pages\ViewVendorApplication::route('/{record}'),
            'edit' => Pages\EditVendorApplication::route('/{record}/edit'),
        ];
    }
}
