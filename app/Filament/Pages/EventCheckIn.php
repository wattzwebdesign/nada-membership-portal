<?php

namespace App\Filament\Pages;

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\EventRegistrationService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class EventCheckIn extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationGroup = 'Events';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Event Check-In';

    protected static string $view = 'filament.pages.event-check-in';

    public ?int $selectedEventId = null;
    public ?string $searchQuery = null;
    public ?array $scanResult = null;
    public array $searchResults = [];

    public function mount(): void
    {
        // Handle QR scan redirect
        $token = request()->query('scan');
        if ($token) {
            $this->processScan($token);
        }
    }

    public function getEvents(): \Illuminate\Support\Collection
    {
        return Event::where('status', 'published')
            ->orderBy('start_date', 'desc')
            ->get()
            ->mapWithKeys(fn ($event) => [$event->id => $event->title . ' (' . $event->start_date->format('M j, Y') . ')']);
    }

    public function processScan(string $token): void
    {
        $service = app(EventRegistrationService::class);
        $registration = $service->checkInByToken($token, auth()->user());

        if ($registration) {
            $this->scanResult = [
                'success' => true,
                'name' => $registration->full_name,
                'registration_number' => $registration->registration_number,
                'event' => $registration->event->title,
            ];

            Notification::make()
                ->title('Checked in: ' . $registration->full_name)
                ->success()
                ->send();
        } else {
            // Check if already checked in
            $existing = EventRegistration::where('qr_code_token', $token)->first();
            if ($existing && $existing->isCheckedIn()) {
                $this->scanResult = [
                    'success' => false,
                    'message' => 'Already checked in at ' . $existing->checked_in_at->format('g:i A'),
                    'name' => $existing->full_name,
                ];
            } else {
                $this->scanResult = [
                    'success' => false,
                    'message' => 'Invalid or expired QR code.',
                ];
            }

            Notification::make()
                ->title($this->scanResult['message'])
                ->danger()
                ->send();
        }
    }

    public function search(): void
    {
        $this->searchResults = [];

        if (empty($this->searchQuery)) {
            return;
        }

        $query = EventRegistration::with('event')
            ->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->searchQuery}%")
                    ->orWhere('last_name', 'like', "%{$this->searchQuery}%")
                    ->orWhere('email', 'like', "%{$this->searchQuery}%")
                    ->orWhere('registration_number', 'like', "%{$this->searchQuery}%");
            })
            ->where('status', '!=', RegistrationStatus::Canceled->value);

        if ($this->selectedEventId) {
            $query->where('event_id', $this->selectedEventId);
        }

        $this->searchResults = $query->limit(20)->get()->toArray();
    }

    public function manualCheckIn(int $registrationId): void
    {
        $registration = EventRegistration::find($registrationId);

        if (! $registration || $registration->isCheckedIn()) {
            Notification::make()
                ->title('Already checked in or not found.')
                ->warning()
                ->send();
            return;
        }

        $registration->update([
            'checked_in_at' => now(),
            'checked_in_by' => auth()->id(),
            'status' => RegistrationStatus::Attended,
        ]);

        Notification::make()
            ->title('Checked in: ' . $registration->full_name)
            ->success()
            ->send();

        $this->search();
    }

    public function undoCheckIn(int $registrationId): void
    {
        $registration = EventRegistration::find($registrationId);

        if (! $registration || ! $registration->isCheckedIn()) {
            Notification::make()
                ->title('Registration not found or not checked in.')
                ->warning()
                ->send();
            return;
        }

        $registration->update([
            'checked_in_at' => null,
            'checked_in_by' => null,
            'status' => RegistrationStatus::Registered,
        ]);

        Notification::make()
            ->title('Check-in reversed: ' . $registration->full_name)
            ->success()
            ->send();

        $this->search();
    }

    public function getStats(): array
    {
        $query = EventRegistration::query()
            ->where('status', '!=', RegistrationStatus::Canceled->value);

        if ($this->selectedEventId) {
            $query->where('event_id', $this->selectedEventId);
        }

        $total = $query->count();
        $checkedIn = (clone $query)->whereNotNull('checked_in_at')->count();

        return [
            'total' => $total,
            'checked_in' => $checkedIn,
            'remaining' => $total - $checkedIn,
        ];
    }
}
