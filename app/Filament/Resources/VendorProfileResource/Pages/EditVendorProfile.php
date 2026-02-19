<?php

namespace App\Filament\Resources\VendorProfileResource\Pages;

use App\Filament\Resources\VendorProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorProfile extends EditRecord
{
    protected static string $resource = VendorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
