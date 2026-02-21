<?php

namespace App\Filament\Resources\EventRegistrationResource\Pages;

use App\Filament\Exports\EventRegistrationExporter;
use App\Filament\Resources\EventRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventRegistrations extends ListRecords
{
    protected static string $resource = EventRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(EventRegistrationExporter::class),
        ];
    }
}
