<?php

namespace App\Filament\Resources\ClinicalResource\Pages;

use App\Filament\Resources\ClinicalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClinicals extends ListRecords
{
    protected static string $resource = ClinicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
