<?php

namespace App\Observers;

use App\Enums\RegistrationStatus;
use App\Enums\TrainingStatus;
use App\Enums\TrainingType;
use App\Models\Training;
use App\Services\GeocodingService;
use App\Services\WalletPassService;
use Illuminate\Support\Facades\Log;

class TrainingObserver
{
    public function saving(Training $training): void
    {
        if (! $training->isDirty('location_address')) {
            return;
        }

        if (! $training->hasPhysicalLocation()) {
            return;
        }

        if (empty($training->location_address)) {
            $training->latitude = null;
            $training->longitude = null;
            return;
        }

        $result = app(GeocodingService::class)->geocode($training->location_address);

        if ($result) {
            $training->latitude = $result['latitude'];
            $training->longitude = $result['longitude'];
        } else {
            Log::info('Geocoding returned no results for training address.', [
                'training_id' => $training->id,
                'address' => $training->location_address,
            ]);
        }
    }

    public function updated(Training $training): void
    {
        $walletRelevantFields = ['title', 'start_date', 'end_date', 'location_name', 'location_address', 'latitude', 'longitude', 'virtual_link', 'type'];

        // Training canceled → void all registrant passes
        if ($training->isDirty('status') && $training->status === TrainingStatus::Canceled) {
            $training->registrations()
                ->where('status', '!=', RegistrationStatus::Canceled->value)
                ->with('walletPasses')
                ->each(function ($registration) {
                    app(WalletPassService::class)->voidTrainingPasses($registration);
                });

            return;
        }

        // Training details changed → update all passes
        if ($training->isDirty($walletRelevantFields)) {
            app(WalletPassService::class)->updateAllPassesForTraining($training);
        }
    }
}
