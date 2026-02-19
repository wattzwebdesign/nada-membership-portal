<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletPass;
use Chiiya\LaravelPasses\Google\GoogleClient;
use Chiiya\Passes\Google\Components\Common\Barcode;
use Chiiya\Passes\Google\Components\Common\Image;
use Chiiya\Passes\Google\Components\Common\LocalizedString;
use Chiiya\Passes\Google\Components\Common\TextModuleData;
use Chiiya\Passes\Google\Enumerators\BarcodeType;
use Chiiya\Passes\Google\Enumerators\MultipleDevicesAndHoldersAllowedStatus;
use Chiiya\Passes\Google\Enumerators\State;
use Chiiya\Passes\Google\Passes\GenericClass;
use Chiiya\Passes\Google\Passes\GenericObject;
use Chiiya\Passes\Google\Repositories\GenericClassRepository;
use Chiiya\Passes\Google\Repositories\GenericObjectRepository;
use Chiiya\Passes\Google\ServiceCredentials;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleWalletService
{
    protected string $issuerId;
    protected string $classId;

    public function __construct()
    {
        $this->issuerId = config('services.google_wallet.issuer_id', '');
        $this->classId = $this->issuerId . '.nada-membership';
    }

    public function ensureClassExists(): void
    {
        $client = new GoogleClient;
        $repo = new GenericClassRepository($client);

        try {
            $repo->get($this->classId);
        } catch (\Exception $e) {
            // Class doesn't exist, create it
            $class = new GenericClass(
                id: $this->classId,
                multipleDevicesAndHoldersAllowedStatus: MultipleDevicesAndHoldersAllowedStatus::MULTIPLE_HOLDERS,
            );

            $repo->create($class);

            Log::info('Google Wallet GenericClass created.', ['class_id' => $this->classId]);
        }
    }

    public function createPassAndGetSaveUrl(User $user): string
    {
        $this->ensureClassExists();

        $passData = app(WalletPassService::class)->buildPassData($user);
        $walletPass = $this->getOrCreateWalletPass($user);

        $objectId = $walletPass->google_object_id;

        $textModules = [
            new TextModuleData(header: 'MEMBER', body: $passData['name'], id: 'member'),
            new TextModuleData(header: 'PLAN', body: $passData['plan'], id: 'plan'),
            new TextModuleData(header: 'VALID THROUGH', body: $passData['expiry'], id: 'expiry'),
        ];

        $barcode = null;
        if ($passData['verify_url']) {
            $barcode = new Barcode(
                type: BarcodeType::QR_CODE,
                value: $passData['verify_url'],
            );
        }

        $object = new GenericObject(
            cardTitle: LocalizedString::make('en', 'NADA'),
            header: LocalizedString::make('en', 'NADA Membership'),
            id: $objectId,
            classId: $this->classId,
            state: State::ACTIVE,
            hexBackgroundColor: '#1C3519',
            logo: Image::make(config('app.url') . '/images/nada-mark.png'),
            barcode: $barcode,
            textModulesData: $textModules,
        );

        // Try create, fall back to update if already exists
        $client = new GoogleClient;
        $repo = new GenericObjectRepository($client);

        try {
            $repo->create($object);
        } catch (\Exception $e) {
            try {
                $repo->update($object);
            } catch (\Exception $e2) {
                Log::error('Failed to create/update Google Wallet object.', [
                    'error' => $e2->getMessage(),
                    'object_id' => $objectId,
                ]);
                throw $e2;
            }
        }

        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => $passData,
        ]);

        return $this->generateSaveUrl($objectId);
    }

    public function updatePassObject(WalletPass $walletPass, User $user): void
    {
        $passData = app(WalletPassService::class)->buildPassData($user);
        $objectId = $walletPass->google_object_id;

        $textModules = [
            new TextModuleData(header: 'MEMBER', body: $passData['name'], id: 'member'),
            new TextModuleData(header: 'PLAN', body: $passData['plan'], id: 'plan'),
            new TextModuleData(header: 'VALID THROUGH', body: $passData['expiry'], id: 'expiry'),
        ];

        $barcode = null;
        if ($passData['verify_url']) {
            $barcode = new Barcode(
                type: BarcodeType::QR_CODE,
                value: $passData['verify_url'],
            );
        }

        $object = new GenericObject(
            cardTitle: LocalizedString::make('en', 'NADA'),
            header: LocalizedString::make('en', 'NADA Membership'),
            id: $objectId,
            classId: $this->classId,
            state: State::ACTIVE,
            hexBackgroundColor: '#1C3519',
            logo: Image::make(config('app.url') . '/images/nada-mark.png'),
            barcode: $barcode,
            textModulesData: $textModules,
        );

        $client = new GoogleClient;
        $repo = new GenericObjectRepository($client);

        try {
            $repo->update($object);
        } catch (\Exception $e) {
            Log::error('Failed to update Google Wallet pass.', [
                'error' => $e->getMessage(),
                'object_id' => $objectId,
            ]);
        }

        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => $passData,
        ]);
    }

    protected function getOrCreateWalletPass(User $user): WalletPass
    {
        $existing = WalletPass::where('user_id', $user->id)
            ->where('platform', 'google')
            ->first();

        if ($existing) {
            return $existing;
        }

        return WalletPass::create([
            'user_id' => $user->id,
            'platform' => 'google',
            'serial_number' => 'NADA-GOOG-' . $user->id . '-' . Str::random(8),
            'google_object_id' => $this->issuerId . '.nada-member-' . $user->id,
            'authentication_token' => Str::random(64),
        ]);
    }

    protected function generateSaveUrl(string $objectId): string
    {
        $credentialsPath = storage_path('app/' . config('services.google_wallet.service_account_path'));
        $credentials = ServiceCredentials::parse($credentialsPath);

        $claims = [
            'iss' => $credentials->client_email,
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'origins' => [config('app.url')],
            'payload' => [
                'genericObjects' => [
                    ['id' => $objectId],
                ],
            ],
        ];

        $jwt = JWT::encode($claims, $credentials->private_key, 'RS256');

        return 'https://pay.google.com/gp/v/save/' . $jwt;
    }
}
