<?php

namespace App\Livewire;

use App\Enums\RegistrationStatus;
use App\Models\Training;
use App\Models\TrainingRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AttendeeList extends Component
{
    public Training $training;

    public $attendees;

    public array $selectedIds = [];

    public function mount(Training $training): void
    {
        abort_unless($training->trainer_id === Auth::id(), 403);

        $this->training = $training;
        $this->loadAttendees();
    }

    public function loadAttendees(): void
    {
        $this->attendees = $this->training->registrations()
            ->with('user')
            ->whereNot('status', RegistrationStatus::Canceled)
            ->orderBy('created_at')
            ->get();
    }

    public function markComplete(int $regId): void
    {
        $registration = TrainingRegistration::where('training_id', $this->training->id)
            ->findOrFail($regId);

        $registration->update([
            'status' => RegistrationStatus::Completed,
            'completed_at' => now(),
            'marked_complete_by' => Auth::id(),
        ]);

        $this->loadAttendees();

        session()->flash('success', 'Attendee marked as completed.');
    }

    public function bulkComplete(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        TrainingRegistration::where('training_id', $this->training->id)
            ->whereIn('id', $this->selectedIds)
            ->update([
                'status' => RegistrationStatus::Completed,
                'completed_at' => now(),
                'marked_complete_by' => Auth::id(),
            ]);

        $this->selectedIds = [];
        $this->loadAttendees();

        session()->flash('success', 'Selected attendees marked as completed.');
    }

    public function render()
    {
        return view('livewire.attendee-list');
    }
}
