<?php

namespace App\Filament\Resources\GroupTrainingRequestResource\Pages;

use App\Filament\Resources\GroupTrainingRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewGroupTrainingRequest extends ViewRecord
{
    protected static string $resource = GroupTrainingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regeneratePaymentLink')
                ->label('Regenerate Payment Link')
                ->icon('heroicon-o-link')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'pending_payment')
                ->requiresConfirmation()
                ->modalHeading('Regenerate Payment Link')
                ->modalDescription('This will create a new Stripe Checkout session. The previous link will no longer work.')
                ->action(function () {
                    $record = $this->record;
                    $record->load('trainer.stripeAccount');
                    $trainer = $record->trainer;

                    if (! $trainer?->stripeAccount?->charges_enabled) {
                        Notification::make()
                            ->title('Payment link could not be generated')
                            ->body('The trainer does not have Stripe payments enabled.')
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    $checkoutUrl = CreateGroupTrainingRequest::createStripeCheckoutSession($record, $trainer);

                    if ($checkoutUrl) {
                        Notification::make()
                            ->title('New payment link generated')
                            ->body('Payment link: ' . $checkoutUrl)
                            ->success()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}
