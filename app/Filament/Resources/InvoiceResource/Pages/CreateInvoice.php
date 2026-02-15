<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Notifications\InvoiceCreatedNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

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
        $this->record->recalculateTotal();

        $this->record->load('user');
        if ($this->record->user) {
            try {
                $this->record->user->notify(new InvoiceCreatedNotification($this->record));
            } catch (\Throwable $e) {
                Log::error('Failed to send notification: InvoiceCreatedNotification', ['error' => $e->getMessage()]);
            }
        }
    }
}
