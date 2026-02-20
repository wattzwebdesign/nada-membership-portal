<?php

namespace App\Filament\Resources\GroupTrainingRequestResource\Pages;

use App\Filament\Resources\GroupTrainingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGroupTrainingRequests extends ListRecords
{
    protected static string $resource = GroupTrainingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
