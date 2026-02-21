<?php

namespace App\Filament\Resources\ClinicalLogResource\Pages;

use App\Filament\Resources\ClinicalLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClinicalLogs extends ListRecords
{
    protected static string $resource = ClinicalLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
