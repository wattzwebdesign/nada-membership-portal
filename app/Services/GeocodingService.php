<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Geocode an address string using Nominatim (OpenStreetMap).
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $address): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'NADAMembershipPortal/1.0 (' . config('app.url') . ')',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if ($response->failed()) {
                Log::warning('Geocoding request failed', [
                    'address' => $address,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $results = $response->json();

            if (empty($results)) {
                Log::info('Geocoding returned no results', ['address' => $address]);
                return null;
            }

            return [
                'latitude' => (float) $results[0]['lat'],
                'longitude' => (float) $results[0]['lon'],
            ];
        } catch (\Exception $e) {
            Log::warning('Geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Build a geocodable address string from parts.
     */
    public function buildAddressString(
        ?string $city = null,
        ?string $state = null,
        ?string $zip = null,
        ?string $country = null,
    ): string {
        return collect([$city, $state, $zip, $country])
            ->filter()
            ->implode(', ');
    }
}
