<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupTrainingRequestResource\Pages;
use App\Models\GroupTrainingRequest;
use App\Models\User;
use App\Services\GroupTrainingFeeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GroupTrainingRequestResource extends Resource
{
    protected static ?string $model = GroupTrainingRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Training & Certificates';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'training_name';

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
        $feeService = app(GroupTrainingFeeService::class);

        return $form
            ->schema([
                Forms\Components\Section::make('Company Contact')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('company_first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Training Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('trainer_id')
                            ->label('Trainer')
                            ->options(
                                User::whereHas('roles', fn ($q) => $q->where('name', 'registered_trainer'))
                                    ->orderBy('last_name')
                                    ->get()
                                    ->mapWithKeys(fn ($u) => [$u->id => $u->first_name . ' ' . $u->last_name . ($u->stripeAccount?->charges_enabled ? '' : ' (No Stripe)')])
                            )
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('training_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('training_date')
                            ->required()
                            ->minDate(now()->addDay()),
                        Forms\Components\TextInput::make('training_city')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('training_state')
                            ->options(array_combine(
                                $states = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'],
                                $states,
                            ))
                            ->searchable()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Pricing')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('cost_per_ticket')
                            ->label('Cost Per Ticket')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(1.00)
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('number_of_tickets')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(500),
                        Forms\Components\Placeholder::make('fee_info')
                            ->label('Current Fee Configuration')
                            ->content($feeService->getFeeDescription())
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Team Members')
                    ->schema([
                        Forms\Components\Repeater::make('members')
                            ->relationship()
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
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['training_name', 'company_first_name', 'company_last_name', 'company_email'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Contact' => $record->company_email,
            'Status' => $record->status ? ucfirst($record->status) : null,
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Training Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('training_name'),
                        Infolists\Components\TextEntry::make('training_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('training_city'),
                        Infolists\Components\TextEntry::make('training_state'),
                        Infolists\Components\TextEntry::make('trainer.full_name')
                            ->label('Trainer'),
                    ])->columns(2),

                Infolists\Components\Section::make('Company Contact')
                    ->schema([
                        Infolists\Components\TextEntry::make('company_full_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('company_email')
                            ->label('Email'),
                    ])->columns(2),

                Infolists\Components\Section::make('Payment')
                    ->schema([
                        Infolists\Components\TextEntry::make('number_of_tickets')
                            ->label('Tickets'),
                        Infolists\Components\TextEntry::make('cost_per_ticket_cents')
                            ->label('Cost Per Ticket')
                            ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('transaction_fee_cents')
                            ->label('Transaction Fee')
                            ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('total_amount_cents')
                            ->label('Total')
                            ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2)),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending_payment' => 'warning',
                                'failed' => 'danger',
                                'expired' => 'gray',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('paid_at')
                            ->dateTime()
                            ->placeholder('Not yet paid'),
                        Infolists\Components\TextEntry::make('stripe_checkout_session_id')
                            ->label('Stripe Session')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent')
                            ->placeholder('—'),
                    ])->columns(2),

                Infolists\Components\Section::make('Team Members')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('members')
                            ->schema([
                                Infolists\Components\TextEntry::make('full_name')
                                    ->label('Name'),
                                Infolists\Components\TextEntry::make('email'),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('training_name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('company_full_name')
                    ->label('Company Contact')
                    ->searchable(['company_first_name', 'company_last_name'])
                    ->sortable('company_last_name'),
                Tables\Columns\TextColumn::make('trainer.full_name')
                    ->label('Trainer')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('training_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_tickets')
                    ->label('Tickets')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount_cents')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => '$' . number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending_payment' => 'warning',
                        'failed' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroupTrainingRequests::route('/'),
            'create' => Pages\CreateGroupTrainingRequest::route('/create'),
            'view' => Pages\ViewGroupTrainingRequest::route('/{record}'),
        ];
    }
}
