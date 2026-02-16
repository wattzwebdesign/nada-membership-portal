<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Console\Command;

class GeocodeTrainers extends Command
{
    protected $signature = 'nada:geocode-trainers {--force : Re-geocode trainers that already have coordinates}';
    protected $description = 'Geocode trainer addresses to populate latitude/longitude';

    public function handle(GeocodingService $geocodingService): int
    {
        $query = User::trainersPublic()
            ->whereNotNull('city')
            ->where('city', '!=', '');

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });
        }

        $trainers = $query->get();

        if ($trainers->isEmpty()) {
            $this->info('No trainers to geocode.');
            return self::SUCCESS;
        }

        $this->info("Geocoding {$trainers->count()} trainer(s)...");
        $bar = $this->output->createProgressBar($trainers->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($trainers as $trainer) {
            $address = $geocodingService->buildAddressString(
                $trainer->city,
                $trainer->state,
                $trainer->zip,
                $trainer->country,
            );

            $coordinates = $geocodingService->geocode($address);

            if ($coordinates) {
                $trainer->update([
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                ]);
                $success++;
            } else {
                $this->line(" Failed: {$trainer->full_name} ({$address})");
                $failed++;
            }

            $bar->advance();

            // Nominatim rate limit: max 1 request per second
            sleep(1);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done! Success: {$success}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
