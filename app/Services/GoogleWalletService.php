<?php

namespace App\Services;

use App\Enums\TrainingType;
use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Models\User;
use App\Models\WalletPass;
use Chiiya\LaravelPasses\Google\GoogleClient;
use Chiiya\Passes\Google\Components\Common\Barcode;
use Chiiya\Passes\Google\Components\Common\Image;
use Chiiya\Passes\Google\Components\Common\LatLongPoint;
use Chiiya\Passes\Google\Components\Common\LocalizedString;
use Chiiya\Passes\Google\Components\Common\TextModuleData;
use Chiiya\Passes\Google\Components\EventTicket\EventDateTime;
use Chiiya\Passes\Google\Components\EventTicket\EventReservationInfo;
use Chiiya\Passes\Google\Components\EventTicket\EventVenue;
use Chiiya\Passes\Google\Enumerators\BarcodeType;
use Chiiya\Passes\Google\Enumerators\MultipleDevicesAndHoldersAllowedStatus;
use Chiiya\Passes\Google\Enumerators\State;
use Chiiya\Passes\Google\Passes\EventTicketClass;
use Chiiya\Passes\Google\Passes\EventTicketObject;
use Chiiya\Passes\Google\Passes\GenericClass;
use Chiiya\Passes\Google\Passes\GenericObject;
use Chiiya\Passes\Google\Repositories\EventTicketClassRepository;
use Chiiya\Passes\Google\Repositories\EventTicketObjectRepository;
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
            ->where('pass_category', 'membership')
            ->first();

        if ($existing) {
            return $existing;
        }

        return WalletPass::create([
            'user_id' => $user->id,
            'platform' => 'google',
            'pass_category' => 'membership',
            'serial_number' => 'NADA-GOOG-' . $user->id . '-' . Str::random(8),
            'google_object_id' => $this->issuerId . '.nada-member-' . $user->id,
            'authentication_token' => Str::random(64),
        ]);
    }

    // ------------------------------------------------------------------
    // Training Event Ticket Methods
    // ------------------------------------------------------------------

    public function ensureTrainingClassExists(Training $training): string
    {
        $trainingClassId = $this->issuerId . '.nada-training-' . $training->id;

        $client = new GoogleClient;
        $repo = new EventTicketClassRepository($client);

        $venue = null;
        if ($training->hasPhysicalLocation()) {
            $locationName = $training->location_name ?: 'Training Venue';
            $locationAddress = $training->location_address ?: $locationName;
            $venue = new EventVenue(
                name: LocalizedString::make('en', $locationName),
                address: LocalizedString::make('en', $locationAddress),
            );
        }

        $dateTime = new EventDateTime(
            start: $training->start_date->toIso8601String(),
            end: $training->end_date->toIso8601String(),
        );

        $class = new EventTicketClass(
            eventName: LocalizedString::make('en', $training->title),
            id: $trainingClassId,
            reviewStatus: 'UNDER_REVIEW',
            issuerName: 'NADA',
            hexBackgroundColor: '#1C3519',
            logo: Image::make(config('app.url') . '/images/nada-mark.png'),
            venue: $venue,
            dateTime: $dateTime,
            multipleDevicesAndHoldersAllowedStatus: MultipleDevicesAndHoldersAllowedStatus::MULTIPLE_HOLDERS,
        );

        try {
            $repo->create($class);
        } catch (\Exception $e) {
            try {
                $repo->update($class);
            } catch (\Exception $e2) {
                Log::error('Failed to create/update Google EventTicketClass.', [
                    'error' => $e2->getMessage(),
                    'class_id' => $trainingClassId,
                ]);
                throw $e2;
            }
        }

        return $trainingClassId;
    }

    public function createTrainingPassAndGetSaveUrl(TrainingRegistration $registration): string
    {
        $registration->load(['training.trainer', 'user']);
        $training = $registration->training;
        $user = $registration->user;

        $trainingClassId = $this->ensureTrainingClassExists($training);
        $walletPass = $this->getOrCreateTrainingWalletPass($registration);

        $objectId = $walletPass->google_object_id;

        $textModules = [
            new TextModuleData(header: 'Trainer', body: $training->trainer->full_name ?? 'N/A', id: 'trainer'),
            new TextModuleData(header: 'Training Type', body: $training->type->label(), id: 'training_type'),
        ];

        if (in_array($training->type, [TrainingType::Virtual, TrainingType::Hybrid]) && $training->virtual_link) {
            $textModules[] = new TextModuleData(header: 'Virtual Meeting Link', body: $training->virtual_link, id: 'virtual_link');
        }

        $locations = [];
        if ($training->hasPhysicalLocation() && $training->hasCoordinates()) {
            $locations[] = new LatLongPoint(
                latitude: $training->latitude,
                longitude: $training->longitude,
            );
        }

        $object = new EventTicketObject(
            id: $objectId,
            classId: $trainingClassId,
            state: State::ACTIVE,
            hexBackgroundColor: '#1C3519',
            ticketHolderName: $user->full_name,
            ticketNumber: 'REG-' . $registration->id,
            reservationInfo: new EventReservationInfo(
                confirmationCode: 'NADA-' . $registration->id,
            ),
            textModulesData: $textModules,
            locations: $locations,
        );

        $client = new GoogleClient;
        $repo = new EventTicketObjectRepository($client);

        try {
            $repo->create($object);
        } catch (\Exception $e) {
            try {
                $repo->update($object);
            } catch (\Exception $e2) {
                Log::error('Failed to create/update Google EventTicketObject.', [
                    'error' => $e2->getMessage(),
                    'object_id' => $objectId,
                ]);
                throw $e2;
            }
        }

        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => [
                'training_id' => $training->id,
                'training_title' => $training->title,
                'start_date' => $training->start_date->toIso8601String(),
                'attendee' => $user->full_name,
            ],
        ]);

        return $this->generateTrainingSaveUrl($objectId);
    }

    public function updateTrainingPassObject(WalletPass $walletPass): void
    {
        $registration = $walletPass->trainingRegistration;
        if (! $registration) {
            return;
        }

        $registration->load(['training.trainer', 'user']);
        $training = $registration->training;
        $user = $registration->user;

        $trainingClassId = $this->ensureTrainingClassExists($training);
        $objectId = $walletPass->google_object_id;

        $textModules = [
            new TextModuleData(header: 'Trainer', body: $training->trainer->full_name ?? 'N/A', id: 'trainer'),
            new TextModuleData(header: 'Training Type', body: $training->type->label(), id: 'training_type'),
        ];

        if (in_array($training->type, [TrainingType::Virtual, TrainingType::Hybrid]) && $training->virtual_link) {
            $textModules[] = new TextModuleData(header: 'Virtual Meeting Link', body: $training->virtual_link, id: 'virtual_link');
        }

        $locations = [];
        if ($training->hasPhysicalLocation() && $training->hasCoordinates()) {
            $locations[] = new LatLongPoint(
                latitude: $training->latitude,
                longitude: $training->longitude,
            );
        }

        $object = new EventTicketObject(
            id: $objectId,
            classId: $trainingClassId,
            state: State::ACTIVE,
            hexBackgroundColor: '#1C3519',
            ticketHolderName: $user->full_name,
            ticketNumber: 'REG-' . $registration->id,
            reservationInfo: new EventReservationInfo(
                confirmationCode: 'NADA-' . $registration->id,
            ),
            textModulesData: $textModules,
            locations: $locations,
        );

        $client = new GoogleClient;
        $repo = new EventTicketObjectRepository($client);

        try {
            $repo->update($object);
        } catch (\Exception $e) {
            Log::error('Failed to update Google training pass.', [
                'error' => $e->getMessage(),
                'object_id' => $objectId,
            ]);
        }

        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => [
                'training_id' => $training->id,
                'training_title' => $training->title,
                'start_date' => $training->start_date->toIso8601String(),
                'attendee' => $user->full_name,
            ],
        ]);
    }

    public function voidTrainingPassObject(WalletPass $walletPass): void
    {
        $objectId = $walletPass->google_object_id;
        if (! $objectId) {
            return;
        }

        $client = new GoogleClient;
        $repo = new EventTicketObjectRepository($client);

        try {
            $existing = $repo->get($objectId);
            $existing->state = State::EXPIRED;
            $repo->update($existing);

            Log::info('Google training pass voided.', ['object_id' => $objectId]);
        } catch (\Exception $e) {
            Log::error('Failed to void Google training pass.', [
                'error' => $e->getMessage(),
                'object_id' => $objectId,
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Event Pass Methods
    // ------------------------------------------------------------------

    public function ensureEventClassExists(\App\Models\Event $event): string
    {
        $eventClassId = $this->issuerId . '.nada-event-' . $event->id;

        $client = new GoogleClient;
        $repo = new EventTicketClassRepository($client);

        $venue = null;
        if ($event->location_name) {
            $venue = new EventVenue(
                name: LocalizedString::make('en', $event->location_name),
                address: LocalizedString::make('en', $event->location_address ?: $event->location_name),
            );
        }

        $dateTime = new EventDateTime(
            start: $event->start_date->toIso8601String(),
            end: $event->end_date->toIso8601String(),
        );

        $class = new EventTicketClass(
            eventName: LocalizedString::make('en', $event->title),
            id: $eventClassId,
            reviewStatus: 'UNDER_REVIEW',
            issuerName: 'NADA',
            hexBackgroundColor: '#1C3519',
            logo: Image::make(config('app.url') . '/images/nada-mark.png'),
            venue: $venue,
            dateTime: $dateTime,
            multipleDevicesAndHoldersAllowedStatus: MultipleDevicesAndHoldersAllowedStatus::MULTIPLE_HOLDERS,
        );

        try {
            $repo->create($class);
        } catch (\Exception $e) {
            try {
                $repo->update($class);
            } catch (\Exception $e2) {
                Log::error('Failed to create/update Google Event EventTicketClass.', [
                    'error' => $e2->getMessage(),
                    'class_id' => $eventClassId,
                ]);
                throw $e2;
            }
        }

        return $eventClassId;
    }

    public function createEventPassAndGetSaveUrl(\App\Models\EventRegistration $registration): string
    {
        $registration->load(['event']);
        $event = $registration->event;

        $eventClassId = $this->ensureEventClassExists($event);
        $walletPass = $this->getOrCreateEventWalletPass($registration);

        $objectId = $walletPass->google_object_id;
        $checkInUrl = route('filament.admin.pages.event-check-in') . '?scan=' . $registration->qr_code_token;

        $textModules = [
            new TextModuleData(header: 'Registration #', body: $registration->registration_number, id: 'reg_number'),
        ];

        $locations = [];
        if ($event->latitude && $event->longitude) {
            $locations[] = new LatLongPoint(
                latitude: (float) $event->latitude,
                longitude: (float) $event->longitude,
            );
        }

        $barcode = new Barcode(
            type: BarcodeType::QR_CODE,
            value: $checkInUrl,
        );

        $object = new EventTicketObject(
            id: $objectId,
            classId: $eventClassId,
            state: State::ACTIVE,
            hexBackgroundColor: '#1C3519',
            ticketHolderName: $registration->full_name,
            ticketNumber: $registration->registration_number,
            barcode: $barcode,
            reservationInfo: new EventReservationInfo(
                confirmationCode: $registration->registration_number,
            ),
            textModulesData: $textModules,
            locations: $locations,
        );

        $client = new GoogleClient;
        $repo = new EventTicketObjectRepository($client);

        try {
            $repo->create($object);
        } catch (\Exception $e) {
            try {
                $repo->update($object);
            } catch (\Exception $e2) {
                Log::error('Failed to create/update Google Event EventTicketObject.', [
                    'error' => $e2->getMessage(),
                    'object_id' => $objectId,
                ]);
                throw $e2;
            }
        }

        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'start_date' => $event->start_date->toIso8601String(),
                'attendee' => $registration->full_name,
            ],
        ]);

        return $this->generateTrainingSaveUrl($objectId);
    }

    public function updateEventPassObject(WalletPass $walletPass): void
    {
        $registration = $walletPass->eventRegistration;
        if (! $registration) {
            return;
        }

        $registration->load(['event']);
        $event = $registration->event;

        $eventClassId = $this->ensureEventClassExists($event);
        $objectId = $walletPass->google_object_id;
        $checkInUrl = route('filament.admin.pages.event-check-in') . '?scan=' . $registration->qr_code_token;

        $textModules = [
            new TextModuleData(header: 'Registration #', body: $registration->registration_number, id: 'reg_number'),
        ];

        $locations = [];
        if ($event->latitude && $event->longitude) {
            $locations[] = new LatLongPoint(
                latitude: (float) $event->latitude,
                longitude: (float) $event->longitude,
            );
        }

        $barcode = new Barcode(
            type: BarcodeType::QR_CODE,
            value: $checkInUrl,
        );

        $object = new EventTicketObject(
            id: $objectId,
            classId: $eventClassId,
            state: State::ACTIVE,
            hexBackgroundColor: '#1C3519',
            ticketHolderName: $registration->full_name,
            ticketNumber: $registration->registration_number,
            barcode: $barcode,
            reservationInfo: new EventReservationInfo(
                confirmationCode: $registration->registration_number,
            ),
            textModulesData: $textModules,
            locations: $locations,
        );

        $client = new GoogleClient;
        $repo = new EventTicketObjectRepository($client);

        try {
            $repo->update($object);
        } catch (\Exception $e) {
            Log::error('Failed to update Google event pass.', [
                'error' => $e->getMessage(),
                'object_id' => $objectId,
            ]);
        }

        $walletPass->update([
            'last_updated_at' => now(),
            'metadata' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'start_date' => $event->start_date->toIso8601String(),
                'attendee' => $registration->full_name,
            ],
        ]);
    }

    public function voidEventPassObject(WalletPass $walletPass): void
    {
        $objectId = $walletPass->google_object_id;
        if (! $objectId) {
            return;
        }

        $client = new GoogleClient;
        $repo = new EventTicketObjectRepository($client);

        try {
            $existing = $repo->get($objectId);
            $existing->state = State::EXPIRED;
            $repo->update($existing);

            Log::info('Google event pass voided.', ['object_id' => $objectId]);
        } catch (\Exception $e) {
            Log::error('Failed to void Google event pass.', [
                'error' => $e->getMessage(),
                'object_id' => $objectId,
            ]);
        }
    }

    protected function getOrCreateEventWalletPass(\App\Models\EventRegistration $registration): WalletPass
    {
        $existing = WalletPass::where('event_registration_id', $registration->id)
            ->where('platform', 'google')
            ->first();

        if ($existing) {
            return $existing;
        }

        return WalletPass::create([
            'user_id' => $registration->user_id,
            'platform' => 'google',
            'pass_category' => 'event',
            'event_registration_id' => $registration->id,
            'serial_number' => 'NADA-GEVT-' . $registration->id . '-' . Str::random(8),
            'google_object_id' => $this->issuerId . '.nada-event-reg-' . $registration->id,
            'authentication_token' => Str::random(64),
        ]);
    }

    protected function getOrCreateTrainingWalletPass(TrainingRegistration $registration): WalletPass
    {
        $existing = WalletPass::where('training_registration_id', $registration->id)
            ->where('platform', 'google')
            ->first();

        if ($existing) {
            return $existing;
        }

        return WalletPass::create([
            'user_id' => $registration->user_id,
            'platform' => 'google',
            'pass_category' => 'training',
            'training_registration_id' => $registration->id,
            'serial_number' => 'NADA-GTRN-' . $registration->id . '-' . Str::random(8),
            'google_object_id' => $this->issuerId . '.nada-training-reg-' . $registration->id,
            'authentication_token' => Str::random(64),
        ]);
    }

    protected function generateTrainingSaveUrl(string $objectId): string
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
                'eventTicketObjects' => [
                    ['id' => $objectId],
                ],
            ],
        ];

        $jwt = JWT::encode($claims, $credentials->private_key, 'RS256');

        return 'https://pay.google.com/gp/v/save/' . $jwt;
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
