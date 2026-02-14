<?php

namespace App\Filament\Resources\PayoutSettingResource\Pages;

use App\Filament\Resources\PayoutSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayoutSettings extends ListRecords
{
    protected static string $resource = PayoutSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
