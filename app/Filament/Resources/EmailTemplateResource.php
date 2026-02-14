<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Used internally to identify this template. Do not change unless you know what you are doing.')
                            ->disabled(fn (?EmailTemplate $record): bool => $record !== null),
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive templates will fall back to the default hardcoded email.')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Email Content')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Supports variables like {{user_name}}'),
                        Forms\Components\TextInput::make('greeting')
                            ->maxLength(255)
                            ->placeholder('Hello {{user_name}}!')
                            ->helperText('The greeting line at the top of the email.'),
                        Forms\Components\Textarea::make('body')
                            ->required()
                            ->rows(6)
                            ->helperText('Each line becomes a paragraph. Use {{variable}} for dynamic content.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('action_text')
                            ->label('Button Text')
                            ->maxLength(255)
                            ->placeholder('e.g., View Dashboard'),
                        Forms\Components\TextInput::make('action_url')
                            ->label('Button URL')
                            ->maxLength(255)
                            ->placeholder('e.g., /dashboard')
                            ->helperText('Relative path or full URL. Supports {{variables}}.'),
                        Forms\Components\Textarea::make('outro')
                            ->label('Footer Text')
                            ->rows(2)
                            ->helperText('Displayed below the button.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Available Variables')
                    ->schema([
                        Forms\Components\Placeholder::make('variables_info')
                            ->label('')
                            ->content(function (?EmailTemplate $record): string {
                                if (!$record || empty($record->available_variables)) {
                                    return 'Save the template to see available variables.';
                                }

                                return collect($record->available_variables)
                                    ->map(fn ($var) => '{{' . $var . '}}')
                                    ->implode('   ');
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Template Info')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('key')
                            ->copyable()
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('—'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                    ])->columns(2),

                Infolists\Components\Section::make('Email Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('subject')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('greeting'),
                        Infolists\Components\TextEntry::make('body')
                            ->columnSpanFull()
                            ->markdown(),
                        Infolists\Components\TextEntry::make('action_text')
                            ->label('Button Text')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('action_url')
                            ->label('Button URL')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('outro')
                            ->label('Footer Text')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Available Variables')
                    ->schema([
                        Infolists\Components\TextEntry::make('available_variables')
                            ->label('')
                            ->formatStateUsing(function (?array $state): string {
                                if (empty($state)) {
                                    return 'None';
                                }
                                return collect($state)->map(fn ($var) => '{{' . $var . '}}')->implode('   ');
                            })
                            ->badge()
                            ->separator('   ')
                            ->color('info'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'view' => Pages\ViewEmailTemplate::route('/{record}'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
