<?php

namespace App\Livewire;

use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CertificateList extends Component
{
    public $certificates;

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->loadCertificates();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadCertificates();
    }

    public function loadCertificates(): void
    {
        $query = Certificate::where('user_id', Auth::id())
            ->orderByDesc('date_issued');

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $this->certificates = $query->get();
    }

    public function render()
    {
        return view('livewire.certificate-list');
    }
}
