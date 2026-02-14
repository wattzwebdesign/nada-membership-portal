<?php

namespace App\Filament\Resources\PayoutSettingResource\Pages;

use App\Filament\Resources\PayoutSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayoutSetting extends EditRecord
{
    protected static string $resource = PayoutSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
