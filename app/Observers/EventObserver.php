<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    public function saving(Event $event): void
    {
        // Skip geocoding if lat/lng were already set (e.g. from Google Places autocomplete)
        if ($event->isDirty(['latitude', 'longitude']) && $event->latitude && $event->longitude) {
            return;
        }

        // Only geocode when address fields change
        $addressFields = ['location_address', 'city', 'state', 'zip', 'country'];
        if (! $event->isDirty($addressFields)) {
            return;
        }

        $addressString = collect([
            $event->location_address,
            $event->city,
            $event->state,
            $event->zip,
            $event->country,
        ])->filter()->implode(', ');

        if (empty($addressString)) {
            $event->latitude = null;
            $event->longitude = null;
            return;
        }

        $result = app(GeocodingService::class)->geocode($addressString);

        if ($result) {
            $event->latitude = $result['latitude'];
            $event->longitude = $result['longitude'];
        } else {
            Log::info('Geocoding returned no results for event address.', [
                'event_id' => $event->id,
                'address' => $addressString,
            ]);
        }
    }
}
