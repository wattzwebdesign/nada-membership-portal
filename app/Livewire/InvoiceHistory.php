<?php

namespace App\Livewire;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InvoiceHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $invoices = Invoice::where('user_id', Auth::id())
            ->orderByDesc('paid_at')
            ->paginate(15);

        return view('livewire.invoice-history', [
            'invoices' => $invoices,
        ]);
    }
}
