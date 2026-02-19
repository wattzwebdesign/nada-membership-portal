<?php

namespace App\Filament\Resources\CheckoutFieldConfigResource\Pages;

use App\Filament\Resources\CheckoutFieldConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckoutFieldConfig extends EditRecord
{
    protected static string $resource = CheckoutFieldConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
