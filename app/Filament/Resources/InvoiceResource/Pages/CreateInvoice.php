<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate amount_due from line items
        $items = $data['items'] ?? [];
        $data['amount_due'] = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
        $data['amount_paid'] = 0;
        $data['currency'] = 'usd';

        return $data;
    }
}
