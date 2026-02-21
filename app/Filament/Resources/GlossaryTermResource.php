<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlossaryTermResource\Pages;
use App\Models\GlossaryCategory;
use App\Models\GlossaryTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GlossaryTermResource extends Resource
{
    protected static ?string $model = GlossaryTerm::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'term';

    public static function getGloballySearchableAttributes(): array
    {
        return ['term', 'definition'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('glossary_category_id')
                    ->label('Category')
                    ->options(GlossaryCategory::orderBy('sort_order')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('term')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('definition')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_published')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('term')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('definition')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('term')
            ->filters([
                Tables\Filters\SelectFilter::make('glossary_category_id')
                    ->label('Category')
                    ->options(GlossaryCategory::orderBy('sort_order')->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlossaryTerms::route('/'),
            'create' => Pages\CreateGlossaryTerm::route('/create'),
            'edit' => Pages\EditGlossaryTerm::route('/{record}/edit'),
        ];
    }
}
