<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use App\Services\StripeService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Stripe\Exception\ApiErrorException;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $plan = $this->record;

        try {
            $stripe = app(StripeService::class);

            if ($plan->stripe_product_id && $plan->stripe_price_id) {
                $stripe->updateStripeProduct($plan);

                if ($plan->wasChanged(['price_cents', 'currency', 'billing_interval', 'billing_interval_count'])) {
                    $stripe->replaceStripePrice($plan);

                    Notification::make()
                        ->title('Stripe product updated and new price created')
                        ->body('Stripe prices are immutable, so a new price was created and the old one archived.')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Stripe product updated')
                        ->success()
                        ->send();
                }
            } else {
                $stripe->createStripeProductAndPrice($plan);

                Notification::make()
                    ->title('Stripe product and price created')
                    ->success()
                    ->send();
            }
        } catch (ApiErrorException $e) {
            Notification::make()
                ->title('Plan saved, but Stripe sync failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
