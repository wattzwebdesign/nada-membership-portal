<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Split;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(UserExporter::class)
                ->modalWidth('4xl')
                ->form(fn (ExportAction $action): array => [
                    Fieldset::make(__('filament-actions::export.modal.form.columns.label'))
                        ->columns(2)
                        ->schema(function () use ($action): array {
                            return array_map(
                                fn (ExportColumn $column): Split => Split::make([
                                    Forms\Components\Checkbox::make('isEnabled')
                                        ->label($column->getName())
                                        ->hiddenLabel()
                                        ->default($column->isEnabledByDefault())
                                        ->live()
                                        ->grow(false),
                                    Forms\Components\TextInput::make('label')
                                        ->label($column->getName())
                                        ->hiddenLabel()
                                        ->default($column->getLabel())
                                        ->placeholder($column->getLabel())
                                        ->disabled(fn (Forms\Get $get): bool => ! $get('isEnabled'))
                                        ->required(fn (Forms\Get $get): bool => (bool) $get('isEnabled')),
                                ])
                                    ->verticallyAlignCenter()
                                    ->statePath($column->getName()),
                                $action->getExporter()::getColumns(),
                            );
                        })
                        ->statePath('columnMap'),
                ]),
            Actions\CreateAction::make(),
        ];
    }
}
