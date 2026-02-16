<?php

namespace App\Filament\Resources\ClinicalResource\Pages;

use App\Filament\Resources\ClinicalResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewClinical extends ViewRecord
{
    protected static string $resource = ClinicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Clinical Submission')
                ->modalDescription('Are you sure you want to approve this clinical submission?')
                ->visible(fn () => in_array($this->record->status, ['submitted', 'under_review']))
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    Notification::make()
                        ->title('Clinical Submission Approved')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Clinical Submission')
                ->visible(fn () => in_array($this->record->status, ['submitted', 'under_review']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Rejection Reason')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'notes' => $data['notes'],
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    Notification::make()
                        ->title('Clinical Submission Rejected')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status', 'notes', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\EditAction::make(),
        ];
    }
}
