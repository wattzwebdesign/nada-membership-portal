<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle fields that are not in $fillable — apply them directly
        if (array_key_exists('discount_approved', $data)) {
            $this->record->discount_approved = $data['discount_approved'];
            unset($data['discount_approved']);
        }

        return $data;
    }
}
