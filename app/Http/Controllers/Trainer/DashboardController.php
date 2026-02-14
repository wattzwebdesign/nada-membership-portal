<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Enums\RegistrationStatus;
use App\Enums\TrainingStatus;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService,
    ) {}

    /**
     * Show the trainer dashboard with upcoming trainings, recent completions, and earnings summary.
     */
    public function index(Request $request): View
    {
        $trainer = $request->user();

        // Upcoming trainings hosted by this trainer
        $upcomingTrainings = $trainer->trainings()
            ->where('status', TrainingStatus::Published)
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->withCount(['registrations' => function ($query) {
                $query->where('status', '!=', RegistrationStatus::Canceled);
            }])
            ->limit(10)
            ->get();

        // Recent completions across all of the trainer's trainings
        $recentCompletions = $trainer->trainings()
            ->with(['registrations' => function ($query) {
                $query->where('status', RegistrationStatus::Completed)
                    ->with('user')
                    ->orderByDesc('completed_at')
                    ->limit(15);
            }])
            ->get()
            ->flatMap(fn ($training) => $training->registrations)
            ->sortByDesc('completed_at')
            ->take(15);

        // Earnings summary from the PayoutService
        $earningsSummary = $this->payoutService->getEarningsReport($trainer);

        return view('trainer.dashboard', [
            'trainer' => $trainer,
            'upcomingTrainings' => $upcomingTrainings,
            'recentCompletions' => $recentCompletions,
            'earningsSummary' => $earningsSummary,
        ]);
    }
}
