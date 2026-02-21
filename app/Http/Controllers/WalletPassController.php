<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Services\AppleWalletService;
use App\Services\WalletPassService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WalletPassController extends Controller
{
    public function __construct(
        protected WalletPassService $walletPassService,
        protected AppleWalletService $appleWalletService,
    ) {}

    /**
     * Download Apple Wallet .pkpass file (authenticated).
     */
    public function downloadApplePass(Request $request)
    {
        $user = $request->user();

        try {
            $pkpass = $this->walletPassService->generateApplePass($user);
        } catch (\Exception $e) {
            Log::error('Apple Wallet pass generation failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Unable to generate your wallet pass. Please try again or contact support.');
        }

        if (empty($pkpass)) {
            Log::error('Apple Wallet pass generation returned empty content.', ['user_id' => $user->id]);

            return back()->with('error', 'Unable to generate your wallet pass. Please try again or contact support.');
        }

        Log::info('Apple Wallet pass generated.', [
            'user_id' => $user->id,
            'size' => strlen($pkpass),
        ]);

        return response($pkpass, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'inline; filename="nada-membership.pkpass"',
            'Content-Length' => strlen($pkpass),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Redirect to Google Wallet save URL (authenticated).
     */
    public function getGooglePassUrl(Request $request)
    {
        $user = $request->user();
        $url = $this->walletPassService->generateGooglePassUrl($user);

        return redirect()->away($url);
    }

    /**
     * Download Apple Wallet .pkpass file for a training registration.
     */
    public function downloadAppleTrainingPass(Request $request, Training $training)
    {
        $user = $request->user();

        $registration = TrainingRegistration::where('training_id', $training->id)
            ->where('user_id', $user->id)
            ->where('status', RegistrationStatus::Registered->value)
            ->first();

        if (! $registration) {
            return back()->with('error', 'You are not registered for this training.');
        }

        try {
            $pkpass = $this->walletPassService->generateAppleTrainingPass($registration);
        } catch (\Exception $e) {
            Log::error('Apple training pass generation failed.', [
                'user_id' => $user->id,
                'training_id' => $training->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to generate your training pass. Please try again or contact support.');
        }

        if (empty($pkpass)) {
            return back()->with('error', 'Unable to generate your training pass. Please try again or contact support.');
        }

        return response($pkpass, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'inline; filename="nada-training.pkpass"',
            'Content-Length' => strlen($pkpass),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Redirect to Google Wallet save URL for a training registration.
     */
    public function getGoogleTrainingPassUrl(Request $request, Training $training)
    {
        $user = $request->user();

        $registration = TrainingRegistration::where('training_id', $training->id)
            ->where('user_id', $user->id)
            ->where('status', RegistrationStatus::Registered->value)
            ->first();

        if (! $registration) {
            return back()->with('error', 'You are not registered for this training.');
        }

        try {
            $url = $this->walletPassService->generateGoogleTrainingPassUrl($registration);
        } catch (\Exception $e) {
            Log::error('Google training pass generation failed.', [
                'user_id' => $user->id,
                'training_id' => $training->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to generate your training pass. Please try again or contact support.');
        }

        return redirect()->away($url);
    }

    /**
     * Download Apple Wallet .pkpass file for an event registration.
     */
    public function downloadAppleEventPass(Request $request, \App\Models\Event $event)
    {
        $user = $request->user();

        $registration = \App\Models\EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', RegistrationStatus::Registered->value)
            ->first();

        if (! $registration) {
            return back()->with('error', 'You are not registered for this event.');
        }

        try {
            $pkpass = $this->walletPassService->generateAppleEventPass($registration);
        } catch (\Exception $e) {
            Log::error('Apple event pass generation failed.', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to generate your event pass. Please try again or contact support.');
        }

        if (empty($pkpass)) {
            return back()->with('error', 'Unable to generate your event pass. Please try again or contact support.');
        }

        return response($pkpass, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'inline; filename="nada-event.pkpass"',
            'Content-Length' => strlen($pkpass),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Redirect to Google Wallet save URL for an event registration.
     */
    public function getGoogleEventPassUrl(Request $request, \App\Models\Event $event)
    {
        $user = $request->user();

        $registration = \App\Models\EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', RegistrationStatus::Registered->value)
            ->first();

        if (! $registration) {
            return back()->with('error', 'You are not registered for this event.');
        }

        try {
            $url = $this->walletPassService->generateGoogleEventPassUrl($registration);
        } catch (\Exception $e) {
            Log::error('Google event pass generation failed.', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to generate your event pass. Please try again or contact support.');
        }

        return redirect()->away($url);
    }

    // ------------------------------------------------------------------
    // Apple Web Service Callbacks (token-verified, no auth middleware)
    // ------------------------------------------------------------------

    /**
     * Register a device to receive push notifications for a pass.
     * POST /api/wallet/apple/v1/devices/{deviceLibraryId}/registrations/{passTypeId}/{serialNumber}
     */
    public function registerDevice(
        Request $request,
        string $deviceLibraryId,
        string $passTypeId,
        string $serialNumber
    ): Response {
        $authToken = $this->extractAppleAuthToken($request);
        if (! $authToken) {
            return response('', 401);
        }

        $body = $request->json();
        $pushToken = $body->get('pushToken', '');

        $status = $this->appleWalletService->registerDevice(
            $deviceLibraryId,
            $passTypeId,
            $serialNumber,
            $pushToken,
            $authToken
        );

        return response('', $status);
    }

    /**
     * Unregister a device.
     * DELETE /api/wallet/apple/v1/devices/{deviceLibraryId}/registrations/{passTypeId}/{serialNumber}
     */
    public function unregisterDevice(
        Request $request,
        string $deviceLibraryId,
        string $passTypeId,
        string $serialNumber
    ): Response {
        $authToken = $this->extractAppleAuthToken($request);
        if (! $authToken) {
            return response('', 401);
        }

        $status = $this->appleWalletService->unregisterDevice(
            $deviceLibraryId,
            $passTypeId,
            $serialNumber,
            $authToken
        );

        return response('', $status);
    }

    /**
     * Get serial numbers of passes registered to a device.
     * GET /api/wallet/apple/v1/devices/{deviceLibraryId}/registrations/{passTypeId}
     */
    public function getUpdatedSerials(
        Request $request,
        string $deviceLibraryId,
        string $passTypeId
    ): Response {
        $passesUpdatedSince = $request->query('passesUpdatedSince');

        $result = $this->appleWalletService->getSerialNumbers(
            $deviceLibraryId,
            $passTypeId,
            $passesUpdatedSince
        );

        if (! $result) {
            return response('', 204);
        }

        return response(json_encode($result), 200, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Get the latest version of a pass.
     * GET /api/wallet/apple/v1/passes/{passTypeId}/{serialNumber}
     */
    public function getLatestPass(
        Request $request,
        string $passTypeId,
        string $serialNumber
    ): Response {
        $authToken = $this->extractAppleAuthToken($request);
        if (! $authToken) {
            return response('', 401);
        }

        $pkpass = $this->appleWalletService->getLatestPass($passTypeId, $serialNumber, $authToken);

        if (! $pkpass) {
            return response('', 401);
        }

        return response($pkpass, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
        ]);
    }

    /**
     * Log errors reported by Apple devices.
     * POST /api/wallet/apple/v1/log
     */
    public function logErrors(Request $request): Response
    {
        $logs = $request->json('logs', []);

        foreach ($logs as $logEntry) {
            Log::warning('Apple Wallet device error', ['message' => $logEntry]);
        }

        return response('', 200);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function extractAppleAuthToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (str_starts_with($header, 'ApplePass ')) {
            return substr($header, 10);
        }

        return null;
    }
}
