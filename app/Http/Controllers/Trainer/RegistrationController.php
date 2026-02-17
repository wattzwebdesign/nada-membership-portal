<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\TrainingRegistration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $trainer = $request->user();

        $query = TrainingRegistration::whereHas('training', function ($q) use ($trainer) {
            $q->where('trainer_id', $trainer->id);
        })->with(['user', 'training']);

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by training
        if ($trainingId = $request->input('training')) {
            $query->where('training_id', $trainingId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $registrations = $query->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $trainings = $trainer->trainings()
            ->orderByDesc('start_date')
            ->get();

        return view('trainer.registrations.index', [
            'registrations' => $registrations,
            'trainings' => $trainings,
            'statuses' => RegistrationStatus::cases(),
        ]);
    }
}
