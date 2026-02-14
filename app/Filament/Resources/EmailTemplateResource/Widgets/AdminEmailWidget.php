<?php

namespace App\Filament\Resources\EmailTemplateResource\Widgets;

use App\Models\SiteSetting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class AdminEmailWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.admin-email-widget';

    protected int | string | array $columnSpan = 'full';

    public ?string $admin_notification_email = '';

    public function mount(): void
    {
        $this->admin_notification_email = SiteSetting::adminEmail();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('admin_notification_email')
                    ->label('Admin Notification Email')
                    ->email()
                    ->required()
                    ->helperText('All admin notification emails (discount requests, trainer applications, clinical submissions, etc.) will be sent to this address.')
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('save')
                            ->icon('heroicon-m-check')
                            ->action(fn () => $this->save())
                    ),
            ]);
    }

    public function save(): void
    {
        $this->validate([
            'admin_notification_email' => ['required', 'email'],
        ]);

        SiteSetting::set('admin_notification_email', $this->admin_notification_email);

        Notification::make()
            ->title('Admin email updated')
            ->success()
            ->send();
    }
}
