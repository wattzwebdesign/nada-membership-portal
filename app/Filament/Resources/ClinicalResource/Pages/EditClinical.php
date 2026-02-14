<?php

namespace App\Filament\Resources\ClinicalResource\Pages;

use App\Filament\Resources\ClinicalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClinical extends EditRecord
{
    protected static string $resource = ClinicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
