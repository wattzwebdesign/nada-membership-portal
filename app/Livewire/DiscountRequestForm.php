<?php

namespace App\Livewire;

use App\Enums\DiscountType;
use App\Models\DiscountRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class DiscountRequestForm extends Component
{
    use WithFileUploads;

    public string $discount_type = 'student';
    public string $proof_description = '';
    public $proof_documents = [];

    public function submit(): void
    {
        $this->validate([
            'discount_type' => 'required|in:student,senior',
            'proof_description' => 'required|string|max:2000',
            'proof_documents' => 'required|array|min:1',
            'proof_documents.*' => 'file|max:10240',
        ]);

        $token = Str::random(64);

        $discountRequest = DiscountRequest::create([
            'user_id' => Auth::id(),
            'discount_type' => $this->discount_type,
            'proof_description' => $this->proof_description,
            'status' => 'pending',
            'approval_token' => $token,
            'token_expires_at' => now()->addDays(30),
        ]);

        foreach ($this->proof_documents as $file) {
            $discountRequest->addMedia($file->getRealPath())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('proof_documents');
        }

        session()->flash('success', 'Your discount request has been submitted and is pending review.');

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.discount-request-form');
    }
}
