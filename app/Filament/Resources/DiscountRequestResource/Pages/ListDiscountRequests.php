<?php

namespace App\Filament\Resources\DiscountRequestResource\Pages;

use App\Filament\Resources\DiscountRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscountRequests extends ListRecords
{
    protected static string $resource = DiscountRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
