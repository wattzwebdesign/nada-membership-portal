<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected GeocodingService $geocodingService,
    ) {}

    public function edit(Request $request): View
    {
        $trainer = $request->user();

        return view('trainer.profile', [
            'trainer' => $trainer,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $trainer = $request->user();

        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $trainer->update($validated);

        // Re-geocode if location fields changed
        if ($trainer->wasChanged(['city', 'state', 'zip', 'country'])) {
            $address = $this->geocodingService->buildAddressString(
                $trainer->city,
                $trainer->state,
                $trainer->zip,
                $trainer->country,
            );

            if ($address) {
                $coordinates = $this->geocodingService->geocode($address);
                $trainer->update([
                    'latitude' => $coordinates['latitude'] ?? null,
                    'longitude' => $coordinates['longitude'] ?? null,
                ]);
            }
        }

        return redirect()->route('trainer.profile.edit')
            ->with('success', 'Your public profile has been updated.');
    }
}
