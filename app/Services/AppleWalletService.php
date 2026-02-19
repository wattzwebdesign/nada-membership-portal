<?php

namespace App\Services;

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

        $pass = new PKPass(
            storage_path('app/' . config('services.apple_wallet.certificate_path')),
            config('services.apple_wallet.certificate_password')
        );

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
            'webServiceURL' => config('services.apple_wallet.web_service_url'),
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
                ],
            ];
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

    public function getOrCreateWalletPass(User $user): WalletPass
    {
        $existing = WalletPass::where('user_id', $user->id)
            ->where('platform', 'apple')
            ->first();

        if ($existing) {
            return $existing;
        }

        return WalletPass::create([
            'user_id' => $user->id,
            'platform' => 'apple',
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
