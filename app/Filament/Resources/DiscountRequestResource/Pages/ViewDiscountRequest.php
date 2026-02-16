<?php

namespace App\Filament\Resources\DiscountRequestResource\Pages;

use App\Filament\Resources\DiscountRequestResource;
use App\Notifications\DiscountApprovedNotification;
use App\Notifications\DiscountDeniedNotification;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewDiscountRequest extends ViewRecord
{
    protected static string $resource = DiscountRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Discount Request')
                ->modalDescription('This will approve the discount and update the user\'s discount status.')
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

                    $this->record->user->update([
                        'discount_type' => $this->record->discount_type,
                        'discount_approved' => true,
                        'discount_approved_at' => now(),
                        'discount_approved_by' => auth()->id(),
                    ]);

                    try {
                        $this->record->user->notify(new DiscountApprovedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send notification: DiscountApprovedNotification', ['error' => $e->getMessage()]);
                    }

                    Notification::make()
                        ->title('Discount Request Approved')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\Action::make('deny')
                ->label('Deny')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Deny Discount Request')
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

                    try {
                        $this->record->user->notify(new DiscountDeniedNotification($this->record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send notification: DiscountDeniedNotification', ['error' => $e->getMessage()]);
                    }

                    Notification::make()
                        ->title('Discount Request Denied')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'reviewed_by', 'reviewed_at']);
                }),
            Actions\EditAction::make(),
        ];
    }
}
