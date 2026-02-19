<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Enums\TrainingStatus;
use App\Models\Agreement;
use App\Models\Training;
use App\Models\TrainingInvitee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    /**
     * Browse all publicly visible trainings (published, non-group).
     */
    public function index(Request $request): View
    {
        $query = Training::publiclyVisible()
            ->with('trainer')
            ->withCount(['registrations' => fn($q) => $q->where('status', '!=', 'canceled')]);

        // Optional: filter upcoming only
        if ($request->boolean('upcoming', true)) {
            $query->upcoming();
        }

        // Optional: search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        // Optional: filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Optional: filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->input('date_to'));
        }

        $trainings = $query->orderBy('start_date', 'asc')->paginate(12);

        return view('trainings.index', compact('trainings'));
    }

    /**
     * Show details for a single training.
     */
    public function show(Request $request, Training $training): View
    {
        // Access control for group trainings
        if ($training->is_group) {
            // Accept token query param — store in session and redirect to login if needed
            $token = $request->query('token');
            if ($token) {
                $invitee = TrainingInvitee::where('token', $token)
                    ->where('training_id', $training->id)
                    ->first();

                if (!$invitee) {
                    abort(403, 'Invalid invitation link.');
                }

                // Store the validated token in session for post-login redirect
                session(['group_training_token' => $token, 'group_training_id' => $training->id]);

                if (!$request->user()) {
                    return redirect()->guest(route('login'));
                }
            }

            // Must be authenticated
            if (!$request->user()) {
                return redirect()->guest(route('login'));
            }

            // Allow trainer who owns the training
            $isTrainer = $request->user()->id === $training->trainer_id;

            // Allow invited users (match by email)
            $isInvited = $training->invitees()
                ->where('email', strtolower($request->user()->email))
                ->exists();

            if (!$isTrainer && !$isInvited) {
                abort(403, 'You are not invited to this training.');
            }
        } else {
            // Non-group trainings must be published
            if ($training->status !== TrainingStatus::Published) {
                abort(404);
            }
        }

        $training->load(['trainer', 'registrations']);

        $spotsRemaining = $training->spotsRemaining();
        $isFull = $training->isFull();

        // Check if the authenticated user (if any) is already registered
        $userRegistration = null;
        $hasTrainingWalletPass = false;
        if ($request->user()) {
            $userRegistration = $training->registrations()
                ->where('user_id', $request->user()->id)
                ->where('status', '!=', RegistrationStatus::Canceled->value)
                ->first();

            if ($userRegistration) {
                $hasTrainingWalletPass = $userRegistration->walletPasses()->exists();
            }
        }

        $activeTerms = Agreement::getActiveTerms();

        return view('trainings.show', compact('training', 'spotsRemaining', 'isFull', 'userRegistration', 'hasTrainingWalletPass', 'activeTerms'));
    }
}
