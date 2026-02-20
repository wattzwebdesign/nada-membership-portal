<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SiteSetting;
use App\Models\TrainerApplication;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\TrainerApplicationSubmittedNotification;
use App\Services\StripeService;
use App\Services\TermsConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class TrainerApplicationController extends Controller
{
    use SafelyNotifies;

    public function __construct(
        protected StripeService $stripeService,
        protected TermsConsentService $termsConsentService,
    ) {}

    /**
     * Show the trainer application form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $activeTerms = Agreement::getActiveTerms();

        return view('account.upgrade-to-trainer', compact('user', 'activeTerms'));
    }

    /**
     * Submit a trainer application — validate, store temp files, redirect to Stripe.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Prevent submission if already a trainer
        if ($user->isTrainer()) {
            return redirect()->route('dashboard')
                ->with('info', 'You are already a Registered Trainer.');
        }

        // Prevent duplicate pending applications
        $hasPending = $user->trainerApplications()->where('status', 'pending')->exists();
        if ($hasPending) {
            return back()->with('error', 'You already have a pending trainer application.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'letter_of_nomination' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'application_submission' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'accept_terms' => ['required', 'accepted'],
        ]);

        // Update user profile fields
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Store uploaded files to temp location
        $letterPath = $request->file('letter_of_nomination')->store('tmp/trainer-applications');
        $applicationPath = $request->file('application_submission')->store('tmp/trainer-applications');

        // Save temp paths in session for retrieval after payment
        $request->session()->put('trainer_application', [
            'letter_path' => $letterPath,
            'letter_name' => $request->file('letter_of_nomination')->getClientOriginalName(),
            'application_path' => $applicationPath,
            'application_name' => $request->file('application_submission')->getClientOriginalName(),
        ]);

        // Record T&C consent
        $signature = $this->termsConsentService->recordConsent($request, $user, 'trainer_application', null, null, 7500);
        $tcMetadata = $this->termsConsentService->stripeMetadata($signature);

        // Create Stripe Checkout session for $75 application fee
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $customer = $this->stripeService->getOrCreateCustomer($user);

            $session = CheckoutSession::create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => 7500,
                        'product_data' => [
                            'name' => 'NADA Trainer Application Fee',
                            'description' => 'One-time application fee for NADA Registered Trainer status',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('trainer-application.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('trainer-application.payment.cancel'),
                'metadata' => array_merge([
                    'user_id' => $user->id,
                    'type' => 'trainer_application',
                ], $tcMetadata),
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error('Failed to create trainer application checkout session', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // Clean up temp files on failure
            $this->cleanupTempFiles($request);

            return back()->with('error', 'Unable to start payment. Please try again or contact support.');
        }
    }

    /**
     * Handle return from successful Stripe Checkout.
     */
    public function paymentSuccess(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        $user = $request->user();

        if (! $sessionId) {
            return redirect()->route('trainer-application.create')
                ->with('error', 'Payment session not found.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = CheckoutSession::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('trainer-application.create')
                    ->with('error', 'Payment was not completed. Please try again.');
            }

            // Idempotency check
            $existing = TrainerApplication::where('stripe_payment_intent_id', $session->payment_intent)->first();
            if ($existing) {
                return redirect()->route('trainer-application.create')
                    ->with('success', 'Your trainer application has been submitted and is pending review.');
            }

            // Retrieve temp file data from session
            $fileData = $request->session()->get('trainer_application');
            if (! $fileData) {
                Log::warning('Trainer application session data missing after payment', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                ]);

                return redirect()->route('trainer-application.create')
                    ->with('error', 'Session expired. Please re-submit your application. You will not be charged again.');
            }

            $amountDollars = $session->amount_total / 100;

            // Create Invoice + InvoiceItem
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'stripe_invoice_id' => $session->payment_intent,
                'status' => 'paid',
                'amount_due' => $amountDollars,
                'amount_paid' => $amountDollars,
                'currency' => $session->currency ?? 'usd',
                'paid_at' => now(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'NADA Trainer Application Fee',
                'quantity' => 1,
                'unit_price' => $amountDollars,
                'total' => $amountDollars,
            ]);

            // Create TrainerApplication
            $application = TrainerApplication::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'stripe_payment_intent_id' => $session->payment_intent,
                'amount_paid_cents' => $session->amount_total,
                'invoice_id' => $invoice->id,
            ]);

            // Attach uploaded files via Spatie Media Library
            $storageDisk = config('filesystems.default', 'local');

            if (isset($fileData['letter_path'])) {
                $application->addMediaFromDisk($fileData['letter_path'], $storageDisk)
                    ->usingFileName($fileData['letter_name'])
                    ->toMediaCollection('letter_of_nomination');
            }

            if (isset($fileData['application_path'])) {
                $application->addMediaFromDisk($fileData['application_path'], $storageDisk)
                    ->usingFileName($fileData['application_name'])
                    ->toMediaCollection('application_submission');
            }

            // Update user status
            $user->trainer_application_status = 'pending';
            $user->save();

            // Clear session data
            $request->session()->forget('trainer_application');

            // Attach Stripe transaction ID to consent signature
            $tcSignatureId = $session->metadata->tc_signature_id ?? null;
            if ($tcSignatureId && $session->payment_intent) {
                TermsConsentService::attachTransaction((int) $tcSignatureId, $session->payment_intent);
            }

            // Notify admin
            $this->safeNotifyRoute(SiteSetting::adminEmail(), new TrainerApplicationSubmittedNotification($application));

            return redirect()->route('trainer-application.create')
                ->with('success', 'Your trainer application has been submitted and is pending review.');
        } catch (\Exception $e) {
            Log::error('Trainer application payment verification failed', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('trainer-application.create')
                ->with('error', 'We could not verify your payment. Please contact support if you were charged.');
        }
    }

    /**
     * Handle Stripe Checkout cancellation.
     */
    public function paymentCancel(Request $request): RedirectResponse
    {
        $this->cleanupTempFiles($request);

        return redirect()->route('trainer-application.create')
            ->with('info', 'Payment was canceled. Your application has not been submitted.');
    }

    /**
     * Clean up temp files stored in session.
     */
    protected function cleanupTempFiles(Request $request): void
    {
        $fileData = $request->session()->pull('trainer_application');

        if ($fileData) {
            $disk = \Illuminate\Support\Facades\Storage::disk(config('filesystems.default', 'local'));

            if (isset($fileData['letter_path']) && $disk->exists($fileData['letter_path'])) {
                $disk->delete($fileData['letter_path']);
            }

            if (isset($fileData['application_path']) && $disk->exists($fileData['application_path'])) {
                $disk->delete($fileData['application_path']);
            }
        }
    }
}
