<?php

namespace App\Filament\Resources\TrainerApplicationResource\Pages;

use App\Filament\Resources\TrainerApplicationResource;
use App\Notifications\TrainerApplicationApprovedNotification;
use App\Notifications\TrainerApplicationDeniedNotification;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewTrainerApplication extends ViewRecord
{
    protected static string $resource = TrainerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Trainer Application')
                ->modalDescription('This will approve the application and assign the registered_trainer role to the user.')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('admin_notes')
                        ->label('Admin Notes (optional)')
                        ->maxLength(2000),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'approved',
                        'admin_notes' => $data['admin_notes'] ?? $this->record->admin_notes,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    $this->record->user->assignRole('registered_trainer');
                    $user = $this->record->user;
                    $user->trainer_application_status = 'approved';
                    $user->trainer_approved_at = now();
                    $user->trainer_approved_by = auth()->id();
                    $user->save();

                    try {
                        $this->record->user->notify(new TrainerApplicationApprovedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send notification: TrainerApplicationApprovedNotification', ['error' => $e->getMessage()]);
                    }

                    Notification::make()
                        ->title('Trainer Application Approved')
                        ->body('The user has been assigned the Registered Trainer role.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\Action::make('deny')
                ->label('Deny')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Deny Trainer Application')
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

                    $this->record->user->trainer_application_status = 'denied';
                    $this->record->user->save();

                    try {
                        $this->record->user->notify(new TrainerApplicationDeniedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send notification: TrainerApplicationDeniedNotification', ['error' => $e->getMessage()]);
                    }

                    Notification::make()
                        ->title('Trainer Application Denied')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\EditAction::make(),
        ];
    }
}
