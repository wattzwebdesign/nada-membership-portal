<?php

namespace App\Filament\Resources;

use App\Enums\DiscountType;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\WalletPassService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'email';

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'email', 'phone'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
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
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\TextInput::make('address_line_1')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_line_2')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zip')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('country')
                            ->maxLength(2)
                            ->default('US'),
                    ])->columns(3)->collapsible(),

                Forms\Components\Section::make('Discount & Trainer Status')
                    ->schema([
                        Forms\Components\Select::make('discount_type')
                            ->options(DiscountType::class)
                            ->default(DiscountType::None),
                        Forms\Components\Toggle::make('discount_approved')
                            ->label('Discount Approved'),
                        Forms\Components\TextInput::make('trainer_application_status')
                            ->maxLength(50)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Managed via Trainer Applications'),
                    ])->columns(2),

                Forms\Components\Section::make('Trainer Profile')
                    ->schema([
                        Forms\Components\Textarea::make('bio')
                            ->label('Bio')
                            ->rows(4)
                            ->maxLength(2000)
                            ->helperText('Public bio displayed on the trainer directory.'),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Auto-populated from address via geocoding.'),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Auto-populated from address via geocoding.'),
                    ])->columns(2)->collapsible(),

                Forms\Components\Section::make('NDA Agreement')
                    ->schema([
                        Forms\Components\DateTimePicker::make('nda_accepted_at')
                            ->label('NDA Accepted At')
                            ->helperText('Clear this field to require the user to re-sign the NDA.'),
                    ]),

                Forms\Components\Section::make('Stripe')
                    ->schema([
                        Forms\Components\TextInput::make('stripe_customer_id')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false),
                    ])->collapsible()->collapsed(),

                Forms\Components\Section::make('Roles')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => ucwords(str_replace('_', ' ', $record->name))),
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
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount_type')
                    ->badge()
                    ->formatStateUsing(fn (DiscountType $state) => $state->label()),
                Tables\Columns\TextColumn::make('trainer_application_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'denied' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucwords(str_replace('_', ' ', $state)))
                    ->separator(','),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Subs')
                    ->sortable(),
                Tables\Columns\IconColumn::make('nda_accepted_at')
                    ->label('NDA Signed')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->nda_accepted_at !== null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('discount_type')
                    ->options(DiscountType::class),
                Tables\Filters\TernaryFilter::make('discount_approved')
                    ->label('Discount Approved'),
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('nda_signed')
                    ->label('NDA Signed')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('nda_accepted_at'),
                        false: fn (Builder $query) => $query->whereNull('nda_accepted_at'),
                    ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('assign_comped_plan')
                        ->label('Assign Comped Plan')
                        ->icon('heroicon-o-gift')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Assign Comped Plan')
                        ->modalDescription('This will create a comped subscription for this user.')
                        ->action(function (User $record) {
                            // Placeholder: implement comped plan assignment logic
                        }),
                    Tables\Actions\Action::make('issue_certificate')
                        ->label('Issue Certificate')
                        ->icon('heroicon-o-document-check')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('training_id')
                                ->label('Training')
                                ->relationship('trainingRegistrations.training', 'title')
                                ->required(),
                        ])
                        ->action(function (User $record, array $data) {
                            // Placeholder: implement certificate issuance logic
                        }),
                    Tables\Actions\Action::make('push_wallet_update')
                        ->label('Push Wallet Update')
                        ->icon('heroicon-o-device-phone-mobile')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Push Wallet Update')
                        ->modalDescription('This will push updated membership data to all wallet passes for this user.')
                        ->visible(fn (User $record): bool => $record->walletPasses()->exists())
                        ->action(function (User $record) {
                            app(WalletPassService::class)->updateAllPassesForUser($record);

                            Notification::make()
                                ->title('Wallet passes updated')
                                ->success()
                                ->send();
                        }),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
