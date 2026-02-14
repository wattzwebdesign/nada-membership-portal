<?php

namespace App\Filament\Resources\TrainingRegistrationResource\Pages;

use App\Filament\Resources\TrainingRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrainingRegistrations extends ListRecords
{
    protected static string $resource = TrainingRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
