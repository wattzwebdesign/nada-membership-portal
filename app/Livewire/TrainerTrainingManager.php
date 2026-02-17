<?php

namespace App\Livewire;

use App\Enums\TrainingStatus;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TrainerTrainingManager extends Component
{
    public $trainings;

    public function mount(): void
    {
        $this->loadTrainings();
    }

    public function loadTrainings(): void
    {
        $this->trainings = Training::where('trainer_id', Auth::id())
            ->withCount(['registrations' => function ($q) {
                $q->whereNot('status', 'canceled');
            }])
            ->orderByDesc('start_date')
            ->get();
    }

    public function cancel(int $id): void
    {
        $training = Training::where('trainer_id', Auth::id())->findOrFail($id);

        if ($training->status === TrainingStatus::Canceled) {
            session()->flash('error', 'This training is already canceled.');
            return;
        }

        $training->update(['status' => TrainingStatus::Canceled]);
        $this->loadTrainings();

        session()->flash('success', 'Training "' . $training->title . '" has been canceled.');
    }

    public function render()
    {
        return view('livewire.trainer-training-manager');
    }
}
