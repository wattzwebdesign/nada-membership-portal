<?php

namespace App\Filament\Resources\GlossaryCategoryResource\Pages;

use App\Filament\Resources\GlossaryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlossaryCategory extends EditRecord
{
    protected static string $resource = GlossaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
