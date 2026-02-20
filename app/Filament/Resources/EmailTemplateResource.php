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
use Illuminate\Support\HtmlString;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'key', 'subject'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Key' => $record->key,
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
                            ->content(function (?EmailTemplate $record): HtmlString {
                                if (!$record || empty($record->available_variables)) {
                                    return new HtmlString('<span class="text-sm text-gray-500">Save the template to see available variables.</span>');
                                }

                                $badges = collect($record->available_variables)
                                    ->map(fn ($var) => '<button type="button" x-on:click="
                                        window.navigator.clipboard.writeText(\'{{' . $var . '}}\');
                                        $tooltip(\'Copied!\', { timeout: 1500 });
                                    " class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 cursor-pointer hover:bg-custom-100 dark:hover:bg-custom-400/20 transition" style="--c-50:var(--info-50);--c-100:var(--info-100);--c-400:var(--info-400);--c-600:var(--info-600);" x-tooltip>{{' . $var . '}}</button>')
                                    ->implode(' ');

                                return new HtmlString('<div class="flex flex-wrap gap-2">' . $badges . '</div>');
                            }),
                    ])
                    ->collapsible(),
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
                            ->html()
                            ->formatStateUsing(function ($state): HtmlString {
                                $variables = is_array($state) ? $state : (is_string($state) ? json_decode($state, true) : []);

                                if (empty($variables)) {
                                    return new HtmlString('<span class="text-sm text-gray-500">None</span>');
                                }

                                $badges = collect($variables)
                                    ->map(fn ($var) => '<button type="button" x-on:click="
                                        window.navigator.clipboard.writeText(\'{{' . $var . '}}\');
                                        $tooltip(\'Copied!\', { timeout: 1500 });
                                    " class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 cursor-pointer hover:bg-custom-100 dark:hover:bg-custom-400/20 transition" style="--c-50:var(--info-50);--c-100:var(--info-100);--c-400:var(--info-400);--c-600:var(--info-600);" x-tooltip>{{' . $var . '}}</button>')
                                    ->implode(' ');

                                return new HtmlString('<div class="flex flex-wrap gap-2">' . $badges . '</div>');
                            }),
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
