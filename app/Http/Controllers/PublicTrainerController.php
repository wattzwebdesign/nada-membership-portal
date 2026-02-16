<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicTrainerController extends Controller
{
    public function __construct(
        protected GeocodingService $geocodingService,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $location = $request->input('location');
        $radius = $request->input('radius');
        $sort = $request->input('sort', 'name_asc');

        // Geocode location search
        $searchLat = null;
        $searchLng = null;
        if ($location) {
            $coordinates = $this->geocodingService->geocode($location);
            if ($coordinates) {
                $searchLat = $coordinates['latitude'];
                $searchLng = $coordinates['longitude'];
                // Default to nearest sort when location is searched
                if (! $request->has('sort')) {
                    $sort = 'nearest';
                }
            }
        }

        $query = User::trainersPublic();

        // Keyword search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        // Add distance calculation when we have search coordinates
        if ($searchLat !== null && $searchLng !== null) {
            $query->selectRaw('users.*, (
                3959 * acos(
                    least(1, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))
                )
            ) as distance', [$searchLat, $searchLng, $searchLat]);

            // Only include trainers with coordinates for proximity search
            $query->whereNotNull('latitude')->whereNotNull('longitude');

            // Radius filter (in miles)
            if ($radius && is_numeric($radius)) {
                $query->havingRaw('distance <= ?', [(float) $radius]);
            }
        }

        // Sort
        if ($sort === 'nearest' && $searchLat !== null) {
            $query->orderBy('distance');
        } else {
            match ($sort) {
                'name_desc' => $query->orderBy('last_name', 'desc')->orderBy('first_name', 'desc'),
                'city' => $query->orderBy('city')->orderBy('last_name'),
                'state' => $query->orderBy('state')->orderBy('last_name'),
                default => $query->orderBy('last_name')->orderBy('first_name'),
            };
        }

        $trainers = $query->paginate(24)->withQueryString();

        // Fetch all trainers with coordinates for map markers
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

        if ($searchLat !== null && $searchLng !== null) {
            $mapQuery->selectRaw('users.*, (
                3959 * acos(
                    least(1, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))
                )
            ) as distance', [$searchLat, $searchLng, $searchLat]);

            if ($radius && is_numeric($radius)) {
                $mapQuery->havingRaw('distance <= ?', [(float) $radius]);
            }
        }

        $mapMarkers = $mapQuery->get()->map(fn (User $trainer) => [
            'id' => $trainer->id,
            'name' => $trainer->full_name,
            'location' => $trainer->location_display,
            'lat' => (float) $trainer->latitude,
            'lng' => (float) $trainer->longitude,
            'url' => route('public.trainers.show', $trainer),
            'distance' => isset($trainer->distance) ? round($trainer->distance, 1) : null,
        ])->values();

        return view('public.trainers.index', [
            'trainers' => $trainers,
            'mapMarkers' => $mapMarkers,
            'googleMapsApiKey' => config('services.google.maps_api_key'),
            'search' => $search,
            'location' => $location,
            'radius' => $radius,
            'sort' => $sort,
            'searchLat' => $searchLat,
            'searchLng' => $searchLng,
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
