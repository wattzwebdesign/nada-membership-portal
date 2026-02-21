<?php

namespace App\Filament\Resources\ClinicalLogResource\Pages;

use App\Filament\Resources\ClinicalLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClinicalLog extends EditRecord
{
    protected static string $resource = ClinicalLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
