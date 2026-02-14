<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;
        $certificates = $user->certificates()->where('status', 'active')->get();
        $upcomingTrainings = $user->trainingRegistrations()
            ->with('training')
            ->whereHas('training', fn($q) => $q->where('start_date', '>', now()))
            ->get();

        return view('dashboard', compact('user', 'subscription', 'certificates', 'upcomingTrainings'));
    }
}
