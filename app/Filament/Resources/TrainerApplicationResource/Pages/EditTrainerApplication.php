<?php

namespace App\Filament\Resources\TrainerApplicationResource\Pages;

use App\Filament\Resources\TrainerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainerApplication extends EditRecord
{
    protected static string $resource = TrainerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
