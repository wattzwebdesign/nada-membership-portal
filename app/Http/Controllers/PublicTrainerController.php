<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTrainerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::trainersPublic();

        // Keyword search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->input('sort', 'name_asc');
        match ($sort) {
            'name_desc' => $query->orderBy('last_name', 'desc')->orderBy('first_name', 'desc'),
            'city' => $query->orderBy('city')->orderBy('last_name'),
            'state' => $query->orderBy('state')->orderBy('last_name'),
            default => $query->orderBy('last_name')->orderBy('first_name'),
        };

        $trainers = $query->paginate(24)->withQueryString();

        // Fetch all trainers with coordinates for map markers (applies same search filter)
        $mapQuery = User::trainersWithLocation();
        if ($search) {
            $mapQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        $mapMarkers = $mapQuery->get()->map(fn (User $trainer) => [
            'id' => $trainer->id,
            'name' => $trainer->full_name,
            'location' => $trainer->location_display,
            'lat' => (float) $trainer->latitude,
            'lng' => (float) $trainer->longitude,
            'url' => route('public.trainers.show', $trainer),
        ])->values();

        return view('public.trainers.index', [
            'trainers' => $trainers,
            'mapMarkers' => $mapMarkers,
            'googleMapsApiKey' => config('services.google.maps_api_key'),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function show(User $user): View
    {
        abort_unless($user->isTrainer(), 404);

        $upcomingTrainings = $user->trainings()
            ->published()
            ->upcoming()
            ->orderBy('start_date')
            ->get();

        return view('public.trainers.show', [
            'trainer' => $user,
            'upcomingTrainings' => $upcomingTrainings,
        ]);
    }
}
