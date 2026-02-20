<?php

namespace App\Filament\Resources\GroupTrainingRequestResource\Pages;

use App\Filament\Resources\GroupTrainingRequestResource;
use App\Models\PayoutSetting;
use App\Models\User;
use App\Services\GroupTrainingFeeService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class CreateGroupTrainingRequest extends CreateRecord
{
    protected static string $resource = GroupTrainingRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $feeService = app(GroupTrainingFeeService::class);

        $costPerTicketCents = (int) round(((float) $data['cost_per_ticket']) * 100);
        unset($data['cost_per_ticket']);

        $data['cost_per_ticket_cents'] = $costPerTicketCents;

        $subtotalCents = $costPerTicketCents * (int) $data['number_of_tickets'];
        $feeCents = $feeService->calculateFeeCents($subtotalCents);

        $data['transaction_fee_cents'] = $feeCents;
        $data['total_amount_cents'] = $subtotalCents + $feeCents;
        $data['status'] = 'pending_payment';

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $record->load('trainer.stripeAccount');

        $trainer = $record->trainer;

        if (! $trainer?->stripeAccount?->charges_enabled) {
            Notification::make()
                ->title('Payment link could not be generated')
                ->body('The selected trainer does not have Stripe payments enabled.')
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $checkoutUrl = static::createStripeCheckoutSession($record, $trainer);

        if ($checkoutUrl) {
            Notification::make()
                ->title('Group training request created')
                ->body('Payment link: ' . $checkoutUrl)
                ->success()
                ->persistent()
                ->send();
        }
    }

    public static function createStripeCheckoutSession(\App\Models\GroupTrainingRequest $record, User $trainer): ?string
    {
        $feeService = app(GroupTrainingFeeService::class);

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $record->cost_per_ticket_cents,
                    'product_data' => [
                        'name' => $record->training_name,
                        'description' => 'Group training ticket — ' . $record->training_date->format('M j, Y') . ' — ' . $record->training_city . ', ' . $record->training_state,
                    ],
                ],
                'quantity' => $record->number_of_tickets,
            ],
        ];

        if ($record->transaction_fee_cents > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $record->transaction_fee_cents,
                    'product_data' => [
                        'name' => 'Transaction Fee',
                        'description' => $feeService->getFeeDescription(),
                    ],
                ],
                'quantity' => 1,
            ];
        }

        $payoutSettings = PayoutSetting::getForTrainer($trainer->id);
        $subtotalCents = $record->subtotal_cents;
        $platformPercentageFee = (int) round($subtotalCents * ($payoutSettings->platform_percentage / 100));
        $applicationFeeAmount = $platformPercentageFee + $record->transaction_fee_cents;

        try {
            $session = CheckoutSession::create([
                'customer_email' => $record->company_email,
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('group-training.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('group-training.cancel'),
                'metadata' => [
                    'type' => 'group_training',
                    'group_training_request_id' => $record->id,
                    'trainer_id' => $trainer->id,
                ],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    'transfer_data' => [
                        'destination' => $trainer->stripeAccount->stripe_connect_account_id,
                    ],
                ],
            ]);

            $record->update([
                'stripe_checkout_session_id' => $session->id,
            ]);

            return $session->url;
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe checkout session for group training', [
                'group_training_request_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Stripe session creation failed')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return null;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
