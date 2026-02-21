<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\FormFieldType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FormFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'formFields';

    protected static ?string $title = 'Custom Form Fields';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options(FormFieldType::class)
                    ->required()
                    ->reactive(),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Unique field identifier (slug)'),
                Forms\Components\TextInput::make('placeholder')
                    ->maxLength(255),
                Forms\Components\Textarea::make('help_text')
                    ->rows(2),
                Forms\Components\TextInput::make('default_value')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_required'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),

                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->required(),
                                Forms\Components\TextInput::make('value')
                                    ->required(),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Toggle::make('is_default'),
                            ])
                            ->columns(4)
                            ->reorderable()
                            ->collapsible(),
                    ])
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                        FormFieldType::Select->value,
                        FormFieldType::MultiSelect->value,
                        FormFieldType::Radio->value,
                        FormFieldType::Checkbox->value,
                    ])),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->boolean()
                    ->label('Required'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
