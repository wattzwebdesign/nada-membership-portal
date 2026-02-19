<?php

namespace App\Services;

use App\Enums\TrainingType;
use App\Models\TrainingRegistration;
use App\Models\User;
use App\Models\WalletPass;
use App\Models\WalletDeviceRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PKPass\PKPass;

class AppleWalletService
{
    public function createPass(User $user): string
    {
        $passData = app(WalletPassService::class)->buildPassData($user);
        $walletPass = $this->getOrCreateWalletPass($user);

        $certPath = storage_path('app/' . config('services.apple_wallet.certificate_path'));
        $certPassword = config('services.apple_wallet.certificate_password');
        $compatibleP12 = $this->ensureCompatibleP12($certPath, $certPassword);

        $pass = new PKPass($compatibleP12, $certPassword);

        $pass->setWwdrCertificatePath(
            storage_path('app/' . config('services.apple_wallet.wwdr_certificate_path'))
        );

        $passDefinition = [
            'formatVersion' => 1,
            'passTypeIdentifier' => config('services.apple_wallet.pass_type_identifier'),
            'serialNumber' => $walletPass->serial_number,
            'teamIdentifier' => config('services.apple_wallet.team_identifier'),
            'organizationName' => 'NADA',
            'description' => 'NADA Membership Card',
            'backgroundColor' => 'rgb(28, 53, 25)',
            'foregroundColor' => 'rgb(255, 255, 255)',
            'labelColor' => 'rgb(221, 173, 38)',
            'authenticationToken' => $walletPass->authentication_token,
            'generic' => [
                'primaryFields' => [
                    [
                        'key' => 'member',
                        'label' => 'MEMBER',
                        'value' => $passData['name'],
                    ],
                ],
                'secondaryFields' => [
                    [
                        'key' => 'plan',
                        'label' => 'PLAN',
                        'value' => $passData['plan'],
                    ],
                    [
                        'key' => 'expiry',
                        'label' => 'VALID THROUGH',
                        'value' => $passData['expiry'],
                    ],
                ],
                'backFields' => [
                    [
                        'key' => 'organization',
                        'label' => 'Organization',
                        'value' => 'National Acupuncture Detoxification Association',
                    ],
                    [
                        'key' => 'memberSince',
                        'label' => 'Member Since',
                        'value' => $passData['member_since'],
                    ],
                ],
            ],
        ];

        // Add certificate code to back fields if available
        if ($passData['certificate_code']) {
            $passDefinition['generic']['backFields'][] = [
                'key' => 'certCode',
                'label' => 'Certificate Code',
                'value' => $passData['certificate_code'],
            ];
        }

        // Add QR barcode if certificate exists
        if ($passData['verify_url']) {
            $passDefinition['barcodes'] = [
                [
                    'format' => 'PKBarcodeFormatQR',
                    'message' => $passData['verify_url'],
                    'messageEncoding' => 'iso-8859-1',
                    'altText' => 'Scan QR to Verify',
                ],
            ];
        }

        // Only include web service URL if configured (null value causes Apple to reject the pass)
        $webServiceUrl = config('services.apple_wallet.web_service_url');
        if ($webServiceUrl) {
            $passDefinition['webServiceURL'] = $webServiceUrl;
        }

        $pass->setData($passDefinition);

        // Add pass images
        $imageDir = storage_path('app/wallet/apple-images');
        foreach (['icon.png', 'icon@2x.png', 'logo.png', 'logo@2x.png'] as $image) {
            $imagePath = $imageDir . '/' . $image;
            if (file_exists($imagePath)) {
                $pass->addFile($imagePath);
            }
        }

        $pkpass = $pass->create();

        // Update pass metadata
        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => $passData,
        ]);

        return $pkpass;
    }

    public function createTrainingPass(TrainingRegistration $registration): string
    {
        $registration->load(['training.trainer', 'user']);
        $training = $registration->training;
        $user = $registration->user;

        $walletPass = $this->getOrCreateTrainingWalletPass($registration);

        $certPath = storage_path('app/' . config('services.apple_wallet.certificate_path'));
        $certPassword = config('services.apple_wallet.certificate_password');
        $compatibleP12 = $this->ensureCompatibleP12($certPath, $certPassword);

        $pass = new PKPass($compatibleP12, $certPassword);

        $pass->setWwdrCertificatePath(
            storage_path('app/' . config('services.apple_wallet.wwdr_certificate_path'))
        );

        // Build time display
        $timeDisplay = $training->start_date->format('g:i A') . ' - ' . $training->end_date->format('g:i A');
        if ($training->timezone) {
            $timeDisplay .= ' ' . $training->timezone;
        }

        // Build location display
        $locationParts = array_filter([
            $training->location_name,
            $training->location_address,
        ]);
        $locationDisplay = implode(', ', $locationParts) ?: 'Virtual';

        $passDefinition = [
            'formatVersion' => 1,
            'passTypeIdentifier' => config('services.apple_wallet.pass_type_identifier'),
            'serialNumber' => $walletPass->serial_number,
            'teamIdentifier' => config('services.apple_wallet.team_identifier'),
            'organizationName' => 'NADA',
            'description' => 'NADA Training: ' . $training->title,
            'backgroundColor' => 'rgb(28, 53, 25)',
            'foregroundColor' => 'rgb(255, 255, 255)',
            'labelColor' => 'rgb(221, 173, 38)',
            'authenticationToken' => $walletPass->authentication_token,
            'relevantDate' => $training->start_date->toIso8601String(),
            'eventTicket' => [
                'primaryFields' => [
                    [
                        'key' => 'training',
                        'label' => 'TRAINING',
                        'value' => $training->title,
                    ],
                ],
                'secondaryFields' => [
                    [
                        'key' => 'date',
                        'label' => 'DATE',
                        'value' => $training->start_date->format('M j, Y'),
                    ],
                    [
                        'key' => 'time',
                        'label' => 'TIME',
                        'value' => $timeDisplay,
                    ],
                ],
                'auxiliaryFields' => [
                    [
                        'key' => 'location',
                        'label' => 'LOCATION',
                        'value' => $locationDisplay,
                    ],
                    [
                        'key' => 'type',
                        'label' => 'TYPE',
                        'value' => $training->type->label(),
                    ],
                ],
                'backFields' => [
                    [
                        'key' => 'trainer',
                        'label' => 'Trainer',
                        'value' => $training->trainer->full_name ?? 'N/A',
                    ],
                    [
                        'key' => 'attendee',
                        'label' => 'Attendee',
                        'value' => $user->full_name,
                    ],
                    [
                        'key' => 'organization',
                        'label' => 'Organization',
                        'value' => 'National Acupuncture Detoxification Association',
                    ],
                ],
            ],
        ];

        // Add virtual link to back fields for virtual/hybrid trainings
        if (in_array($training->type, [TrainingType::Virtual, TrainingType::Hybrid]) && $training->virtual_link) {
            $passDefinition['eventTicket']['backFields'][] = [
                'key' => 'virtualLink',
                'label' => 'Virtual Meeting Link',
                'value' => $training->virtual_link,
            ];
        }

        // Add geofencing location for in-person/hybrid trainings with coordinates
        if ($training->hasPhysicalLocation() && $training->hasCoordinates()) {
            $passDefinition['locations'] = [
                [
                    'latitude' => $training->latitude,
                    'longitude' => $training->longitude,
                    'relevantText' => 'Your NADA training starts soon: ' . $training->title,
                ],
            ];
        }

        // Web service URL for auto-updates
        $webServiceUrl = config('services.apple_wallet.web_service_url');
        if ($webServiceUrl) {
            $passDefinition['webServiceURL'] = $webServiceUrl;
        }

        $pass->setData($passDefinition);

        // Add pass images
        $imageDir = storage_path('app/wallet/apple-images');
        foreach (['icon.png', 'icon@2x.png', 'logo.png', 'logo@2x.png'] as $image) {
            $imagePath = $imageDir . '/' . $image;
            if (file_exists($imagePath)) {
                $pass->addFile($imagePath);
            }
        }

        $pkpass = $pass->create();

        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => [
                'training_id' => $training->id,
                'training_title' => $training->title,
                'start_date' => $training->start_date->toIso8601String(),
                'attendee' => $user->full_name,
            ],
        ]);

        return $pkpass;
    }

    protected function getOrCreateTrainingWalletPass(TrainingRegistration $registration): WalletPass
    {
        $existing = WalletPass::where('training_registration_id', $registration->id)
            ->where('platform', 'apple')
            ->first();

        if ($existing) {
            return $existing;
        }

        return WalletPass::create([
            'user_id' => $registration->user_id,
            'platform' => 'apple',
            'pass_category' => 'training',
            'training_registration_id' => $registration->id,
            'serial_number' => 'NADA-TRN-' . $registration->id . '-' . Str::random(8),
            'pass_type_identifier' => config('services.apple_wallet.pass_type_identifier'),
            'authentication_token' => Str::random(64),
        ]);
    }

    /**
     * Convert a .p12 to a legacy-compatible format if PHP's openssl_pkcs12_read can't handle it.
     * This works around OpenSSL 3.x / PHP 8.4 incompatibilities with newer PKCS12 MAC algorithms.
     */
    protected function ensureCompatibleP12(string $certPath, string $password): string
    {
        // First, test if PHP can read the p12 natively
        $pkcs12 = file_get_contents($certPath);
        $certs = [];
        if ($pkcs12 && openssl_pkcs12_read($pkcs12, $certs, $password)) {
            return $certPath; // Works fine, no conversion needed
        }

        // PHP can't read it — convert using the server's openssl CLI with legacy algorithms
        $compatiblePath = storage_path('app/wallet/pass-compatible.p12');

        // Check if we already have a compatible version that's newer than the source
        if (file_exists($compatiblePath) && filemtime($compatiblePath) >= filemtime($certPath)) {
            return $compatiblePath;
        }

        $tempPem = tempnam(sys_get_temp_dir(), 'pkpass_');
        $escapedPass = escapeshellarg('pass:' . $password);

        // Extract to PEM
        $cmd = sprintf(
            'openssl pkcs12 -in %s -out %s -nodes -passin %s -legacy 2>&1',
            escapeshellarg($certPath),
            escapeshellarg($tempPem),
            $escapedPass
        );
        $output = shell_exec($cmd);

        if (! file_exists($tempPem) || filesize($tempPem) === 0) {
            @unlink($tempPem);
            // Try without -legacy flag (some servers don't have it)
            $cmd = sprintf(
                'openssl pkcs12 -in %s -out %s -nodes -passin %s 2>&1',
                escapeshellarg($certPath),
                escapeshellarg($tempPem),
                $escapedPass
            );
            shell_exec($cmd);
        }

        if (! file_exists($tempPem) || filesize($tempPem) === 0) {
            @unlink($tempPem);
            throw new \RuntimeException('Failed to extract PEM from P12 certificate. OpenSSL output: ' . ($output ?? 'none'));
        }

        // Re-package as legacy P12 with SHA1 MAC (compatible with all PHP/OpenSSL versions)
        $cmd = sprintf(
            'openssl pkcs12 -export -in %s -out %s -passout %s -certpbe PBE-SHA1-3DES -keypbe PBE-SHA1-3DES -macalg SHA1 2>&1',
            escapeshellarg($tempPem),
            escapeshellarg($compatiblePath),
            $escapedPass
        );
        shell_exec($cmd);

        @unlink($tempPem);

        if (! file_exists($compatiblePath) || filesize($compatiblePath) === 0) {
            throw new \RuntimeException('Failed to create compatible P12 certificate.');
        }

        Log::info('Converted P12 certificate to legacy-compatible format.');

        return $compatiblePath;
    }

    public function getOrCreateWalletPass(User $user): WalletPass
    {
        $existing = WalletPass::where('user_id', $user->id)
            ->where('platform', 'apple')
            ->where('pass_category', 'membership')
            ->first();

        if ($existing) {
            return $existing;
        }

        return WalletPass::create([
            'user_id' => $user->id,
            'platform' => 'apple',
            'pass_category' => 'membership',
            'serial_number' => 'NADA-USR-' . $user->id . '-' . Str::random(8),
            'pass_type_identifier' => config('services.apple_wallet.pass_type_identifier'),
            'authentication_token' => Str::random(64),
        ]);
    }

    public function registerDevice(
        string $deviceLibraryId,
        string $passTypeId,
        string $serialNumber,
        string $pushToken,
        string $authToken
    ): int {
        $walletPass = WalletPass::where('serial_number', $serialNumber)
            ->where('pass_type_identifier', $passTypeId)
            ->first();

        if (! $walletPass || $walletPass->authentication_token !== $authToken) {
            return 401;
        }

        $existing = WalletDeviceRegistration::where('wallet_pass_id', $walletPass->id)
            ->where('device_library_identifier', $deviceLibraryId)
            ->first();

        if ($existing) {
            $existing->update(['push_token' => $pushToken]);
            return 200;
        }

        WalletDeviceRegistration::create([
            'wallet_pass_id' => $walletPass->id,
            'device_library_identifier' => $deviceLibraryId,
            'push_token' => $pushToken,
        ]);

        return 201;
    }

    public function unregisterDevice(
        string $deviceLibraryId,
        string $passTypeId,
        string $serialNumber,
        string $authToken
    ): int {
        $walletPass = WalletPass::where('serial_number', $serialNumber)
            ->where('pass_type_identifier', $passTypeId)
            ->first();

        if (! $walletPass || $walletPass->authentication_token !== $authToken) {
            return 401;
        }

        WalletDeviceRegistration::where('wallet_pass_id', $walletPass->id)
            ->where('device_library_identifier', $deviceLibraryId)
            ->delete();

        return 200;
    }

    public function getSerialNumbers(
        string $deviceLibraryId,
        string $passTypeId,
        ?string $passesUpdatedSince = null
    ): ?array {
        $query = WalletPass::where('pass_type_identifier', $passTypeId)
            ->whereHas('deviceRegistrations', function ($q) use ($deviceLibraryId) {
                $q->where('device_library_identifier', $deviceLibraryId);
            });

        if ($passesUpdatedSince) {
            $query->where('last_updated_at', '>', $passesUpdatedSince);
        }

        $passes = $query->get();

        if ($passes->isEmpty()) {
            return null;
        }

        return [
            'serialNumbers' => $passes->pluck('serial_number')->toArray(),
            'lastUpdated' => (string) $passes->max('last_updated_at')?->timestamp,
        ];
    }

    public function getLatestPass(string $passTypeId, string $serialNumber, string $authToken): ?string
    {
        $walletPass = WalletPass::where('serial_number', $serialNumber)
            ->where('pass_type_identifier', $passTypeId)
            ->first();

        if (! $walletPass || $walletPass->authentication_token !== $authToken) {
            return null;
        }

        if ($walletPass->pass_category === 'training') {
            $registration = $walletPass->trainingRegistration;
            if (! $registration) {
                return null;
            }
            return $this->createTrainingPass($registration);
        }

        return $this->createPass($walletPass->user);
    }

    public function pushUpdateToDevices(WalletPass $walletPass): void
    {
        $registrations = $walletPass->deviceRegistrations;

        if ($registrations->isEmpty()) {
            return;
        }

        // Apple Wallet updates use an empty push notification.
        // The device then calls our web service to fetch the updated pass.
        // This requires an APNs p8 key configured in the environment.
        // For now, we update the pass metadata so it's ready when requested.
        $walletPass->update(['last_updated_at' => now()]);

        Log::info('Apple Wallet pass updated, waiting for device pull.', [
            'wallet_pass_id' => $walletPass->id,
            'device_count' => $registrations->count(),
        ]);
    }
}
