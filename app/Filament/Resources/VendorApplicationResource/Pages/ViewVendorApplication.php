<?php

namespace App\Filament\Resources\VendorApplicationResource\Pages;

use App\Filament\Resources\VendorApplicationResource;
use App\Models\User;
use App\Notifications\VendorApplicationApprovedNotification;
use App\Notifications\VendorApplicationDeniedNotification;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ViewVendorApplication extends ViewRecord
{
    protected static string $resource = VendorApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Vendor Application')
                ->modalDescription('This will approve the application and assign the vendor role to the user.')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('admin_notes')
                        ->label('Admin Notes (optional)')
                        ->maxLength(2000),
                ])
                ->action(function (array $data) {
                    // If no user_id, create a new user
                    if (! $this->record->user_id) {
                        $user = User::create([
                            'first_name' => $this->record->first_name,
                            'last_name' => $this->record->last_name,
                            'email' => $this->record->email,
                            'password' => bcrypt(Str::random(16)),
                            'email_verified_at' => now(),
                        ]);

                        $this->record->update(['user_id' => $user->id]);
                    } else {
                        $user = $this->record->user;
                    }

                    $this->record->update([
                        'status' => 'approved',
                        'admin_notes' => $data['admin_notes'] ?? $this->record->admin_notes,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    $user->assignRole('vendor');
                    $user->update([
                        'vendor_application_status' => 'approved',
                    ]);

                    try {
                        $user->notify(new VendorApplicationApprovedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send notification: VendorApplicationApprovedNotification', ['error' => $e->getMessage()]);
                    }

                    Notification::make()
                        ->title('Vendor Application Approved')
                        ->body('The user has been assigned the Vendor role.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\Action::make('deny')
                ->label('Deny')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Deny Vendor Application')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('admin_notes')
                        ->label('Denial Reason')
                        ->required()
                        ->maxLength(2000),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'denied',
                        'admin_notes' => $data['admin_notes'],
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    // Notify the user if they have an account
                    $notifiable = $this->record->user ?? (new User(['email' => $this->record->email]));

                    if ($this->record->user) {
                        $this->record->user->update([
                            'vendor_application_status' => 'denied',
                        ]);
                    }

                    try {
                        $notifiable->notify(new VendorApplicationDeniedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send notification: VendorApplicationDeniedNotification', ['error' => $e->getMessage()]);
                    }

                    Notification::make()
                        ->title('Vendor Application Denied')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\EditAction::make(),
        ];
    }
}
