<?php

namespace App\Filament\Resources\StripeAccountResource\Pages;

use App\Filament\Resources\StripeAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStripeAccount extends EditRecord
{
    protected static string $resource = StripeAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
