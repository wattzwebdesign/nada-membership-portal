<?php

namespace App\Filament\Resources;

use App\Enums\EventStatus;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Events';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Event::where('status', EventStatus::Draft)->count();

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
                Forms\Components\Section::make('Event Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->helperText('Auto-generated from title if left blank'),
                        Forms\Components\Select::make('status')
                            ->options(EventStatus::class)
                            ->default(EventStatus::Draft)
                            ->required(),
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('short_description')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Date & Time')
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

                Forms\Components\Section::make('Registration Window')
                    ->schema([
                        Forms\Components\DateTimePicker::make('registration_start_date')
                            ->helperText('Leave blank to open immediately'),
                        Forms\Components\DateTimePicker::make('registration_end_date')
                            ->helperText('Leave blank to close at event start'),
                    ])->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\ViewField::make('google_autocomplete')
                            ->view('filament.forms.google-address-autocomplete')
                            ->columnSpanFull()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('location_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location_address')
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->helperText('Start typing to search for an address'),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('zip')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('country')
                            ->default('US')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('virtual_link')
                            ->url()
                            ->maxLength(500)
                            ->helperText('For virtual/hybrid events'),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric(),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric(),
                    ])->columns(3),

                Forms\Components\Section::make('Contact & Display')
                    ->schema([
                        Forms\Components\TextInput::make('organizer_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Event'),
                        Forms\Components\FileUpload::make('featured_image_path')
                            ->image()
                            ->directory('events')
                            ->maxSize(5120),
                    ])->columns(2),

                Forms\Components\Section::make('Confirmation')
                    ->schema([
                        Forms\Components\RichEditor::make('confirmation_message')
                            ->helperText('Displayed on the confirmation page after registration')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('confirmation_email_body')
                            ->rows(4)
                            ->helperText('Custom email body for confirmation email')
                            ->columnSpanFull(),
                    ])->collapsed(),
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('max_attendees')
                    ->label('Capacity')
                    ->placeholder('Unlimited'),
                Tables\Columns\TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Registrations')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EventStatus::class),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\Filter::make('upcoming')
                    ->query(fn (Builder $query) => $query->where('start_date', '>', now()))
                    ->label('Upcoming Only')
                    ->toggle(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record) => $record->status === EventStatus::Draft && ! $record->trashed())
                    ->action(function (Event $record) {
                        $record->update([
                            'status' => EventStatus::Published,
                            'published_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Event published')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PricingCategoriesRelationManager::class,
            RelationManagers\FormFieldsRelationManager::class,
            RelationManagers\RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
