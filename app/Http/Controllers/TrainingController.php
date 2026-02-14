<?php

namespace App\Http\Controllers;

use App\Enums\TrainingStatus;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    /**
     * Browse all published trainings with optional filters.
     */
    public function index(Request $request): View
    {
        $query = Training::published()
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

        $trainings = $query->orderBy('start_date', 'asc')->paginate(12);

        return view('trainings.index', compact('trainings'));
    }

    /**
     * Show details for a single training.
     */
    public function show(Request $request, Training $training): View
    {
        $training->load(['trainer', 'registrations']);

        $spotsRemaining = $training->spotsRemaining();
        $isFull = $training->isFull();

        // Check if the authenticated user (if any) is already registered
        $userRegistration = null;
        if ($request->user()) {
            $userRegistration = $training->registrations()
                ->where('user_id', $request->user()->id)
                ->where('status', '!=', 'canceled')
                ->first();
        }

        return view('trainings.show', compact('training', 'spotsRemaining', 'isFull', 'userRegistration'));
    }
}
