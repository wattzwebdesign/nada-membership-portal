<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use App\Services\StripeService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Stripe\Exception\ApiErrorException;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    protected function afterCreate(): void
    {
        $plan = $this->record;

        try {
            $stripe = app(StripeService::class);
            $stripe->createStripeProductAndPrice($plan);

            Notification::make()
                ->title('Stripe product and price created')
                ->success()
                ->send();
        } catch (ApiErrorException $e) {
            Notification::make()
                ->title('Plan saved, but Stripe sync failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
