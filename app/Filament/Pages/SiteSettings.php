<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
    public bool $image_optimization_enabled = true;
    public ?string $image_webp_quality = '80';
    public ?string $image_max_width = '1920';
    public ?string $image_max_height = '1920';
    public ?string $image_thumb_size = '400';
    public bool $umami_enabled = false;
    public ?string $umami_script_url = '';
    public ?string $umami_website_id = '';
    public array $state_law_links = [];

    public array $stripeInfo = [];

    public function mount(): void
    {
        $this->admin_notification_email = SiteSetting::adminEmail();
        $this->group_training_fee_type = SiteSetting::get('group_training_fee_type', 'flat');
        $this->group_training_fee_value = SiteSetting::get('group_training_fee_value', '0');
        $this->image_optimization_enabled = SiteSetting::imageOptimizationEnabled();
        $this->image_webp_quality = SiteSetting::get('image_webp_quality', '80');
        $this->image_max_width = SiteSetting::get('image_max_width', '1920');
        $this->image_max_height = SiteSetting::get('image_max_height', '1920');
        $this->image_thumb_size = SiteSetting::get('image_thumb_size', '400');
        $this->umami_enabled = SiteSetting::umamiEnabled();
        $this->umami_script_url = SiteSetting::get('umami_script_url', '');
        $this->umami_website_id = SiteSetting::get('umami_website_id', '');
        $this->state_law_links = collect(SiteSetting::getJson('state_law_links'))
            ->map(fn ($url, $state) => ['state' => $state, 'url' => $url])
            ->values()
            ->toArray();
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

                Section::make('Image Optimization')
                    ->description('Configure how uploaded images are compressed and converted.')
                    ->schema([
                        Toggle::make('image_optimization_enabled')
                            ->label('Enable Image Optimization')
                            ->helperText('When enabled, uploaded images are compressed client-side and converted to WebP server-side.'),
                        TextInput::make('image_webp_quality')
                            ->label('WebP Quality')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Quality for server-side WebP conversion (1-100). Recommended: 80.'),
                        TextInput::make('image_max_width')
                            ->label('Max Width (px)')
                            ->numeric()
                            ->minValue(100)
                            ->helperText('Client-side: images wider than this are resized before upload.'),
                        TextInput::make('image_max_height')
                            ->label('Max Height (px)')
                            ->numeric()
                            ->minValue(100)
                            ->helperText('Client-side: images taller than this are resized before upload.'),
                        TextInput::make('image_thumb_size')
                            ->label('Thumbnail Size (px)')
                            ->numeric()
                            ->minValue(50)
                            ->maxValue(800)
                            ->helperText('Width & height of generated thumbnail conversions.'),
                    ]),

                Section::make('Umami Analytics')
                    ->description('Configure Umami website analytics tracking. API credentials for the admin analytics dashboard are configured in .env.')
                    ->schema([
                        Toggle::make('umami_enabled')
                            ->label('Enable Umami Tracking')
                            ->helperText('When enabled, the Umami tracking script is added to all pages.'),
                        TextInput::make('umami_script_url')
                            ->label('Tracking Script URL')
                            ->url()
                            ->placeholder('https://analytics.yourdomain.com/script.js')
                            ->helperText('The URL to your Umami tracking script.'),
                        TextInput::make('umami_website_id')
                            ->label('Website ID')
                            ->placeholder('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
                            ->helperText('The Umami Website ID for this site. Also used by the admin analytics dashboard widgets.'),
                    ]),

                Section::make('State Law Links')
                    ->description('Set a link per US state to that state\'s acupuncture law page. Members will see a "Your State Laws" link in the sidebar based on their address.')
                    ->schema([
                        Repeater::make('state_law_links')
                            ->label('')
                            ->schema([
                                Select::make('state')
                                    ->label('State')
                                    ->options(array_combine(
                                        ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'],
                                        ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'],
                                    ))
                                    ->required()
                                    ->searchable(),
                                TextInput::make('url')
                                    ->label('Law Page URL')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://...'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add State')
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['state'] ?? null),
                    ])
                    ->collapsible(),
            ]);
    }

    public function save(): void
    {
        $this->validate([
            'admin_notification_email' => ['required', 'email'],
            'group_training_fee_type' => ['required', 'in:flat,percentage'],
            'group_training_fee_value' => ['required', 'numeric', 'min:0'],
            'image_webp_quality' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'image_max_width' => ['nullable', 'numeric', 'min:100'],
            'image_max_height' => ['nullable', 'numeric', 'min:100'],
            'image_thumb_size' => ['nullable', 'numeric', 'min:50', 'max:800'],
        ]);

        SiteSetting::set('admin_notification_email', $this->admin_notification_email);
        SiteSetting::set('group_training_fee_type', $this->group_training_fee_type);
        SiteSetting::set('group_training_fee_value', $this->group_training_fee_value);
        SiteSetting::set('image_optimization_enabled', $this->image_optimization_enabled ? '1' : '0');
        SiteSetting::set('image_webp_quality', $this->image_webp_quality);
        SiteSetting::set('image_max_width', $this->image_max_width);
        SiteSetting::set('image_max_height', $this->image_max_height);
        SiteSetting::set('image_thumb_size', $this->image_thumb_size);
        SiteSetting::set('umami_enabled', $this->umami_enabled ? '1' : '0');
        SiteSetting::set('umami_script_url', $this->umami_script_url);
        SiteSetting::set('umami_website_id', $this->umami_website_id);

        $linksMap = collect($this->state_law_links)
            ->filter(fn ($item) => ! empty($item['state']) && ! empty($item['url']))
            ->mapWithKeys(fn ($item) => [$item['state'] => $item['url']])
            ->toArray();
        SiteSetting::setJson('state_law_links', $linksMap);

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
