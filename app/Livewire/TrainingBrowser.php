<?php

namespace App\Livewire;

use App\Enums\TrainingStatus;
use App\Enums\TrainingType;
use App\Models\Training;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TrainingBrowser extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $trainings = Training::query()
            ->published()
            ->upcoming()
            ->with('trainer')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('location_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            })
            ->orderBy('start_date')
            ->paginate(12);

        return view('livewire.training-browser', [
            'trainings' => $trainings,
            'trainingTypes' => TrainingType::cases(),
        ]);
    }
}
