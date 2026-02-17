<?php

namespace App\Filament\Resources\TrainingResource\Pages;

use App\Enums\TrainingStatus;
use App\Filament\Resources\TrainingResource;
use App\Notifications\GroupTrainingInviteNotification;
use App\Notifications\TrainingApprovedNotification;
use App\Notifications\TrainingDeniedNotification;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditTraining extends EditRecord
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Training')
                ->modalDescription('This will publish the training and notify the trainer. If this is a group training, invitees will also be notified.')
                ->visible(fn () => $this->record->status === TrainingStatus::PendingApproval)
                ->action(function () {
                    $this->record->update(['status' => TrainingStatus::Published]);

                    try {
                        $this->record->trainer->notify(new TrainingApprovedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send TrainingApprovedNotification', ['error' => $e->getMessage()]);
                    }

                    if ($this->record->is_group) {
                        foreach ($this->record->invitees as $invitee) {
                            try {
                                \Illuminate\Support\Facades\Notification::route('mail', $invitee->email)
                                    ->notify(new GroupTrainingInviteNotification($this->record, $invitee));
                                $invitee->update(['notified_at' => now()]);
                            } catch (\Throwable $e) {
                                Log::error('Failed to send GroupTrainingInviteNotification', [
                                    'email' => $invitee->email,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }

                    Notification::make()->title('Training approved and published.')->success()->send();

                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('deny')
                ->label('Deny')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('denied_reason')
                        ->label('Reason for Denial')
                        ->required()
                        ->maxLength(1000)
                        ->placeholder('Explain why this training is being denied...'),
                ])
                ->modalHeading('Deny Training')
                ->visible(fn () => $this->record->status === TrainingStatus::PendingApproval)
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => TrainingStatus::Denied,
                        'denied_reason' => $data['denied_reason'],
                    ]);

                    try {
                        $this->record->trainer->notify(new TrainingDeniedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send TrainingDeniedNotification', ['error' => $e->getMessage()]);
                    }

                    Notification::make()->title('Training denied.')->success()->send();

                    $this->refreshFormData(['status', 'denied_reason']);
                }),

            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
