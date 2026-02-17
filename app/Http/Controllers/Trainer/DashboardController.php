<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Enums\RegistrationStatus;
use App\Enums\TrainingStatus;
use App\Models\Clinical;
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

        // Trainings pending approval or denied
        $pendingTrainings = $trainer->trainings()
            ->whereIn('status', [TrainingStatus::PendingApproval, TrainingStatus::Denied])
            ->orderByDesc('updated_at')
            ->get();

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

        // Total completions across all trainings
        $totalCompletions = $trainer->trainings()
            ->withCount(['registrations as completions_count' => function ($query) {
                $query->where('status', RegistrationStatus::Completed);
            }])
            ->get()
            ->sum('completions_count');

        // Earnings summary from the PayoutService
        $earningsSummary = $this->payoutService->getEarningsReport($trainer);

        // Clinicals pending review for this trainer
        $pendingClinicals = Clinical::where('trainer_id', $trainer->id)
            ->whereIn('status', ['submitted', 'under_review'])
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('trainer.dashboard', [
            'trainer' => $trainer,
            'pendingTrainings' => $pendingTrainings,
            'upcomingTrainings' => $upcomingTrainings,
            'recentCompletions' => $recentCompletions,
            'totalCompletions' => $totalCompletions,
            'earningsSummary' => $earningsSummary,
            'pendingClinicals' => $pendingClinicals,
        ]);
    }
}
