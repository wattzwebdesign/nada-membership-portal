<?php

namespace App\Livewire;

use App\Models\Certificate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class CertificateVerification extends Component
{
    public string $code = '';

    public ?Certificate $certificate = null;

    public bool $searched = false;

    public function verify(): void
    {
        $this->validate([
            'code' => 'required|string|min:3',
        ]);

        $this->certificate = Certificate::where('certificate_code', $this->code)
            ->with(['user', 'training'])
            ->first();

        $this->searched = true;
    }

    public function render()
    {
        return view('livewire.certificate-verification');
    }
}
