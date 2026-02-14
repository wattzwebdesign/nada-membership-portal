<?php

namespace App\Livewire;

use App\Services\PayoutService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PayoutDashboard extends Component
{
    public array $earnings = [];

    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        // Default to last 30 days
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        $this->loadReport();
    }

    public function loadReport(): void
    {
        $this->validate([
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
        ]);

        $payoutService = app(PayoutService::class);

        $this->earnings = $payoutService->getEarningsReport(
            Auth::user(),
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay(),
        );
    }

    public function render()
    {
        return view('livewire.payout-dashboard');
    }
}
