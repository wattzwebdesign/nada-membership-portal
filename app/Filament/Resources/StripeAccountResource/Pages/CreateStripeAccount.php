<?php

namespace App\Filament\Resources\StripeAccountResource\Pages;

use App\Filament\Resources\StripeAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStripeAccount extends CreateRecord
{
    protected static string $resource = StripeAccountResource::class;
}
