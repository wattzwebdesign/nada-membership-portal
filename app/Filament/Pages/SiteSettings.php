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

    public function mount(): void
    {
        $this->admin_notification_email = SiteSetting::adminEmail();
        $this->group_training_fee_type = SiteSetting::get('group_training_fee_type', 'flat');
        $this->group_training_fee_value = SiteSetting::get('group_training_fee_value', '0');
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
}
