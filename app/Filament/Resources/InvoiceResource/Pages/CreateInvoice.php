<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['amount_paid'] = 0;
        $data['currency'] = 'usd';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalculate amount_due from the saved line items
        $this->record->recalculateTotal();
    }
}
