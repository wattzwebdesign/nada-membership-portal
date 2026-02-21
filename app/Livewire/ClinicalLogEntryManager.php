<?php

namespace App\Livewire;

use App\Enums\ClinicalLogStatus;
use App\Models\ClinicalLog;
use App\Models\ClinicalLogEntry;
use App\Models\SiteSetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClinicalLogEntryManager extends Component
{
    use WithFileUploads;

    public ClinicalLog $clinicalLog;

    public bool $showForm = false;
    public ?int $editingEntryId = null;

    // Form fields
    public string $date = '';
    public string $location = '';
    public string $protocol = '';
    public string|float $hours = '';
    public string $notes = '';
    public array $attachments = [];

    public function mount(ClinicalLog $clinicalLog): void
    {
        $this->clinicalLog = $clinicalLog;
    }

    public function getIsReadOnlyProperty(): bool
    {
        return ! in_array($this->clinicalLog->status, [ClinicalLogStatus::InProgress, ClinicalLogStatus::Rejected]);
    }

    public function getTotalHoursProperty(): float
    {
        return (float) $this->clinicalLog->entries()->sum('hours');
    }

    public function getThresholdProperty(): float
    {
        return (float) SiteSetting::get('clinical_hours_threshold', '40');
    }

    public function getProgressPercentProperty(): float
    {
        if ($this->threshold <= 0) {
            return 100;
        }

        return min(100, round(($this->totalHours / $this->threshold) * 100, 1));
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function addEntry(): void
    {
        $this->validate($this->entryRules());

        $entry = $this->clinicalLog->entries()->create([
            'date' => $this->date,
            'location' => $this->location,
            'protocol' => $this->protocol,
            'hours' => $this->hours,
            'notes' => $this->notes ?: null,
        ]);

        $this->handleAttachments($entry);

        $this->closeForm();
        $this->dispatch('entry-saved');
    }

    public function editEntry(int $id): void
    {
        $entry = $this->clinicalLog->entries()->findOrFail($id);

        $this->editingEntryId = $entry->id;
        $this->date = $entry->date->format('Y-m-d');
        $this->location = $entry->location;
        $this->protocol = $entry->protocol;
        $this->hours = $entry->hours;
        $this->notes = $entry->notes ?? '';
        $this->attachments = [];
        $this->showForm = true;
    }

    public function updateEntry(): void
    {
        $this->validate($this->entryRules());

        $entry = $this->clinicalLog->entries()->findOrFail($this->editingEntryId);

        $entry->update([
            'date' => $this->date,
            'location' => $this->location,
            'protocol' => $this->protocol,
            'hours' => $this->hours,
            'notes' => $this->notes ?: null,
        ]);

        $this->handleAttachments($entry);

        $this->closeForm();
        $this->dispatch('entry-saved');
    }

    public function deleteEntry(int $id): void
    {
        $entry = $this->clinicalLog->entries()->findOrFail($id);
        $entry->clearMediaCollection('entry_attachments');
        $entry->delete();

        $this->dispatch('entry-saved');
    }

    public function removeAttachment(int $entryId, int $mediaId): void
    {
        $entry = $this->clinicalLog->entries()->findOrFail($entryId);
        $media = $entry->getMedia('entry_attachments')->firstWhere('id', $mediaId);

        if ($media) {
            $media->delete();
        }
    }

    protected function handleAttachments(ClinicalLogEntry $entry): void
    {
        if (! empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $entry->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('entry_attachments');
            }
        }
    }

    protected function resetForm(): void
    {
        $this->editingEntryId = null;
        $this->date = '';
        $this->location = '';
        $this->protocol = '';
        $this->hours = '';
        $this->notes = '';
        $this->attachments = [];
        $this->resetValidation();
    }

    protected function entryRules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'location' => ['required', 'string', 'max:255'],
            'protocol' => ['required', 'string', 'max:255'],
            'hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function render()
    {
        $entries = $this->clinicalLog->entries()
            ->with('media')
            ->orderBy('date')
            ->get();

        return view('livewire.clinical-log-entry-manager', [
            'entries' => $entries,
        ]);
    }
}
