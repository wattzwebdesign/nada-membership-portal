<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.site-settings';

    public ?string $admin_notification_email = '';
    public ?string $group_training_fee_type = 'flat';
    public ?string $group_training_fee_value = '0';

    public array $stripeInfo = [];

    public function mount(): void
    {
        $this->admin_notification_email = SiteSetting::adminEmail();
        $this->group_training_fee_type = SiteSetting::get('group_training_fee_type', 'flat');
        $this->group_training_fee_value = SiteSetting::get('group_training_fee_value', '0');
        $this->stripeInfo = $this->fetchStripeInfo();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('admin_notification_email')
                    ->label('Admin Notification Email')
                    ->email()
                    ->required()
                    ->helperText('All admin notification emails will be sent to this address.'),

                Select::make('group_training_fee_type')
                    ->label('Group Training Fee Type')
                    ->options([
                        'flat' => 'Flat Fee ($)',
                        'percentage' => 'Percentage (%)',
                    ])
                    ->required()
                    ->helperText('How the transaction fee is calculated for group training requests.'),

                TextInput::make('group_training_fee_value')
                    ->label('Group Training Fee Value')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->helperText('For flat fee: dollar amount (e.g. 25 = $25.00). For percentage: percent of subtotal (e.g. 5 = 5%).'),
            ]);
    }

    public function save(): void
    {
        $this->validate([
            'admin_notification_email' => ['required', 'email'],
            'group_training_fee_type' => ['required', 'in:flat,percentage'],
            'group_training_fee_value' => ['required', 'numeric', 'min:0'],
        ]);

        SiteSetting::set('admin_notification_email', $this->admin_notification_email);
        SiteSetting::set('group_training_fee_type', $this->group_training_fee_type);
        SiteSetting::set('group_training_fee_value', $this->group_training_fee_value);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function fetchStripeInfo(): array
    {
        $info = [
            'configured' => false,
            'error' => null,
        ];

        $secretKey = config('services.stripe.secret');

        if (! $secretKey) {
            $info['error'] = 'Stripe secret key is not configured in your environment.';
            return $info;
        }

        // Detect mode from key prefix
        $info['configured'] = true;
        $info['mode'] = str_starts_with($secretKey, 'sk_test_') ? 'Test' : 'Live';
        $info['publishable_key_last4'] = $this->maskKey(config('services.stripe.key'));
        $info['secret_key_last4'] = $this->maskKey($secretKey);
        $info['webhook_configured'] = ! empty(config('services.stripe.webhook_secret'));
        $info['connect_webhook_configured'] = ! empty(config('services.stripe.connect_webhook_secret'));

        try {
            Stripe::setApiKey($secretKey);

            // Fetch account info
            $account = \Stripe\Account::retrieve();
            $info['account'] = [
                'id' => $account->id,
                'business_name' => $account->settings?->dashboard?->display_name ?? $account->business_profile?->name ?? 'N/A',
                'country' => $account->country ?? 'N/A',
                'default_currency' => strtoupper($account->default_currency ?? 'N/A'),
                'email' => $account->email ?? 'N/A',
                'charges_enabled' => $account->charges_enabled ?? false,
                'payouts_enabled' => $account->payouts_enabled ?? false,
            ];

            // Fetch webhook endpoints
            $webhooks = \Stripe\WebhookEndpoint::all(['limit' => 20]);
            $info['webhooks'] = [];
            foreach ($webhooks->data as $wh) {
                $info['webhooks'][] = [
                    'id' => $wh->id,
                    'url' => $wh->url,
                    'status' => $wh->status,
                    'enabled_events' => $wh->enabled_events,
                    'api_version' => $wh->api_version,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Stripe info for Site Settings', ['error' => $e->getMessage()]);
            $info['error'] = 'Could not connect to Stripe: ' . $e->getMessage();
        }

        return $info;
    }

    protected function maskKey(?string $key): string
    {
        if (! $key) {
            return 'Not set';
        }

        return substr($key, 0, 7) . '...' . substr($key, -4);
    }
}
