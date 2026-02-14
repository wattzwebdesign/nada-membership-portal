<?php

namespace App\Livewire;

use App\Models\Clinical;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\ClinicalSubmittedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ClinicalsForm extends Component
{
    use WithFileUploads;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $estimated_training_date = '';
    public ?int $trainer_id = null;
    public string $notes = '';
    public $treatment_logs = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->email = $user->email ?? '';
    }

    public function submit(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'estimated_training_date' => 'required|date|after:today',
            'trainer_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:2000',
            'treatment_logs' => 'required|array|min:1',
            'treatment_logs.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $clinical = Clinical::create([
            'user_id' => Auth::id(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'estimated_training_date' => $this->estimated_training_date,
            'trainer_id' => $this->trainer_id,
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        foreach ($this->treatment_logs as $file) {
            $clinical->addMedia($file->getRealPath())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('treatment_logs');
        }

        Notification::route('mail', SiteSetting::adminEmail())
            ->notify(new ClinicalSubmittedNotification($clinical));

        session()->flash('success', 'Your clinical submission has been received and is under review.');

        $this->redirect(route('clinicals.index'));
    }

    public function render()
    {
        $trainers = User::role('registered_trainer')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('livewire.clinicals-form', [
            'trainers' => $trainers,
        ]);
    }
}
