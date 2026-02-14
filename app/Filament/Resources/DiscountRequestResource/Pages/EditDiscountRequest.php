<?php

namespace App\Filament\Resources\DiscountRequestResource\Pages;

use App\Filament\Resources\DiscountRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscountRequest extends EditRecord
{
    protected static string $resource = DiscountRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
