<?php

namespace App\Http\Controllers;

use App\Enums\EventPaymentStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\EventRegistrationConfirmation;
use App\Notifications\NewEventRegistrationNotification;
use App\Services\EventRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EventRegistrationController extends Controller
{
    public function __construct(
        protected EventRegistrationService $registrationService,
    ) {}

    public function store(Request $request, Event $event)
    {
        if (! $event->isRegistrationOpen()) {
            return back()->with('error', 'Registration is not currently open for this event.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'selected_packages' => 'nullable|array',
            'selected_packages.*' => 'exists:event_pricing_packages,id',
            'form_responses' => 'nullable|array',
        ]);

        $user = auth()->user();

        $registration = $this->registrationService->register($event, $validated, $user);

        // If paid event, redirect to Stripe
        if ($registration->total_amount_cents > 0) {
            $checkoutUrl = $this->registrationService->createCheckoutSession($registration);

            return redirect()->away($checkoutUrl);
        }

        // Free event — complete immediately
        $this->sendConfirmationNotifications($registration);

        return redirect()->route('events.confirmation', [
            'event' => $event->slug,
            'registration' => $registration->id,
        ]);
    }

    public function paymentSuccess(Request $request, Event $event)
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('public.events.show', $event->slug);
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('stripe_checkout_session_id', $sessionId)
            ->first();

        if (! $registration) {
            return redirect()->route('public.events.show', $event->slug)
                ->with('error', 'Registration not found.');
        }

        // If webhook hasn't processed yet, process now
        if ($registration->payment_status === EventPaymentStatus::Unpaid) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    $this->registrationService->processPayment($registration, $session->payment_intent);
                    $this->sendConfirmationNotifications($registration->fresh());
                }
            } catch (\Exception $e) {
                Log::error('Event payment success handling failed.', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('events.confirmation', [
            'event' => $event->slug,
            'registration' => $registration->id,
        ]);
    }

    public function confirmation(Event $event, EventRegistration $registration)
    {
        if ($registration->event_id !== $event->id) {
            abort(404);
        }

        $qrCodeBase64 = $this->registrationService->generateQrCode($registration);

        return view('events.confirmation', compact('event', 'registration', 'qrCodeBase64'));
    }

    public function index(Request $request)
    {
        $registrations = $request->user()
            ->eventRegistrations()
            ->with('event')
            ->latest()
            ->paginate(10);

        return view('events.my-registrations', compact('registrations'));
    }

    public function destroy(Request $request, Event $event)
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'registered')
            ->first();

        if (! $registration) {
            return back()->with('error', 'Registration not found.');
        }

        $this->registrationService->cancelRegistration($registration);

        try {
            Notification::route('mail', $registration->email)
                ->notify(new \App\Notifications\EventRegistrationCanceled($registration));
        } catch (\Exception $e) {
            Log::error('Event cancellation notification failed.', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Your registration has been canceled.');
    }

    protected function sendConfirmationNotifications(EventRegistration $registration): void
    {
        try {
            Notification::route('mail', $registration->email)
                ->notify(new EventRegistrationConfirmation($registration));
        } catch (\Exception $e) {
            Log::error('Event confirmation notification failed.', ['error' => $e->getMessage()]);
        }

        try {
            $adminEmail = \App\Models\SiteSetting::adminEmail();
            if ($adminEmail) {
                Notification::route('mail', $adminEmail)
                    ->notify(new NewEventRegistrationNotification($registration));
            }
        } catch (\Exception $e) {
            Log::error('Event admin notification failed.', ['error' => $e->getMessage()]);
        }
    }
}
