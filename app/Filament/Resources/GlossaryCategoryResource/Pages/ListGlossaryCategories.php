<?php

namespace App\Filament\Resources\GlossaryCategoryResource\Pages;

use App\Filament\Resources\GlossaryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlossaryCategories extends ListRecords
{
    protected static string $resource = GlossaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
