<?php

namespace App\Filament\Resources\StripeAccountResource\Pages;

use App\Filament\Resources\StripeAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStripeAccounts extends ListRecords
{
    protected static string $resource = StripeAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
